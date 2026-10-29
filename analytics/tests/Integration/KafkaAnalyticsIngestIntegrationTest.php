<?php

namespace Tests\Integration;

use App\Infrastructure\ClickHouseClientFactory;
use App\Services\AnalyticsEventIngestService;
use App\Services\UserMetricsService;
use Carbon\CarbonImmutable;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

class KafkaAnalyticsIngestIntegrationTest extends ClickHouseIntegrationTestCase
{
    protected function setUp(): void
    {
        if (! getenv('KAFKA_BROKERS')) {
            $this->markTestSkipped('Set KAFKA_BROKERS to run Kafka integration tests.');
        }

        parent::setUp();
    }

    public function test_kafka_message_is_consumed_into_clickhouse_rollups(): void
    {
        $brokers = (string) getenv('KAFKA_BROKERS');
        $topic = 'user.registered';
        $email = 'kafka-it-'.uniqid('', true).'@example.com';
        $occurredAt = CarbonImmutable::parse('2026-10-18 09:30:00', 'UTC')->toIso8601String();

        $payload = json_encode([
            'user_id' => 9001,
            'email' => $email,
            'occurred_at' => $occurredAt,
        ], JSON_THROW_ON_ERROR);

        $this->publishKafkaMessage($brokers, $topic, $payload);

        $ingest = $this->app->make(AnalyticsEventIngestService::class);
        $this->consumeOneMessage($brokers, [$topic], function (ConsumeMessage $message) use ($ingest): void {
            $decoded = json_decode((string) $message->getValue(), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                throw new \RuntimeException('Kafka payload must be a JSON object.');
            }

            $ingest->handle($message->getTopic(), $decoded);
        });

        $report = $this->app->make(UserMetricsService::class)->report(
            CarbonImmutable::parse('2026-10-18', 'UTC')->startOfDay(),
            CarbonImmutable::parse('2026-10-18', 'UTC')->endOfDay(),
        );

        $this->assertGreaterThanOrEqual(1, $report['total']);

        $client = $this->app->make(ClickHouseClientFactory::class)->make();
        $rows = $client->select(
            <<<'SQL'
            SELECT count() AS total
            FROM events_raw
            WHERE topic = {topic:String}
              AND JSONExtractString(payload, 'email') = {email:String}
            SQL,
            [
                'topic' => $topic,
                'email' => $email,
            ],
        )->fetchOne();

        $this->assertSame(1, (int) ($rows['total'] ?? 0));
    }

    private function publishKafkaMessage(string $brokers, string $topic, string $payload): void
    {
        $config = new ProducerConfig;
        $config->setBroker($brokers);
        $config->setUpdateBrokers(true);

        $producer = new Producer($config);
        $producer->send($topic, $payload, 'analytics-it-'.uniqid());
    }

    /**
     * @param  list<string>  $topics
     */
    private function consumeOneMessage(string $brokers, array $topics, callable $handler): void
    {
        $config = new ConsumerConfig;
        $config->setBroker($brokers);
        $config->setUpdateBrokers(true);
        $config->setGroupId('analytics-kafka-it-'.uniqid('', true));
        $config->setClientId('analytics-kafka-it');
        $config->setTopic($topics);
        $config->setInterval(0.2);
        $config->setAutoCommit(true);

        $consumer = new Consumer($config, static function (): void {
        });

        $deadline = microtime(true) + 30.0;
        while (microtime(true) < $deadline) {
            $message = $consumer->consume();
            if ($message instanceof ConsumeMessage) {
                $handler($message);
                $consumer->close();

                return;
            }

            usleep(200_000);
        }

        $consumer->close();
        $this->fail('Timed out waiting for Kafka message.');
    }
}
