<?php

namespace Tests\Integration;

use App\Contracts\LlmClient;
use App\Infrastructure\QdrantProductVectorRepository;
use App\Services\CatalogIndexIngestService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;
use Tests\Support\FixedVectorLlmClient;
use Tests\Support\RequiresRecommendationIntegration;
use Tests\TestCase;

class KafkaCatalogIngestIntegrationTest extends TestCase
{
    use RequiresRecommendationIntegration;

    private string $collectionName;

    protected function setUp(): void
    {
        $this->skipUnlessRecommendationIntegration();

        if (! getenv('KAFKA_BROKERS')) {
            $this->markTestSkipped('Set KAFKA_BROKERS to run Kafka catalog ingest integration tests.');
        }

        parent::setUp();

        $this->prepareIntegrationHttpClient();
        $this->configureIntegrationDatabase();

        config([
            'recommendation.qdrant.host' => getenv('QDRANT_HOST') ?: '127.0.0.1',
            'recommendation.qdrant.port' => (int) (getenv('QDRANT_PORT') ?: 6333),
        ]);

        $suffix = 'kafka_'.substr(md5((string) microtime(true)), 0, 12);
        $this->useIsolatedQdrantCollection($suffix);
        $this->collectionName = (string) config('recommendation.qdrant.collection');

        $this->skipUnlessQdrantReachable();
        $this->skipUnlessPostgresReachable();

        Artisan::call('migrate:fresh', ['--force' => true]);

        $this->app->instance(LlmClient::class, new FixedVectorLlmClient);
    }

    protected function tearDown(): void
    {
        if (isset($this->collectionName)) {
            $host = (string) config('recommendation.qdrant.host');
            $port = (int) config('recommendation.qdrant.port');
            $scheme = config('recommendation.qdrant.use_https') ? 'https' : 'http';
            Http::withOptions(['proxy' => ''])->timeout(5)
                ->delete(sprintf('%s://%s:%d/collections/%s', $scheme, $host, $port, $this->collectionName));
        }

        parent::tearDown();
    }

    public function test_kafka_product_created_indexes_postgres_and_qdrant(): void
    {
        $brokers = (string) getenv('KAFKA_BROKERS');
        $topic = 'product.created';
        $productId = 880_101;

        $payload = [
            'id' => $productId,
            'store_id' => 1,
            'category_id' => 5,
            'name' => 'Kafka Integration Sofa',
            'slug' => 'kafka-integration-sofa',
            'description' => 'Indexed from Kafka integration test',
            'sku' => 'KAFKA-880101',
            'price' => 12000,
            'attributes' => ['color' => 'pink'],
            'image' => 'kafka-sofa.jpg',
            'is_published' => true,
            'in_stock' => true,
        ];

        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->publishKafkaMessage($brokers, $topic, $encoded);

        $ingest = $this->app->make(CatalogIndexIngestService::class);
        $this->consumeOneMessage($brokers, [$topic], function (ConsumeMessage $message) use ($ingest): void {
            $decoded = json_decode((string) $message->getValue(), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                throw new \RuntimeException('Kafka payload must be a JSON object.');
            }

            $ingest->handle($message->getTopic(), $decoded);
        });

        $row = DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', 1)
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('Kafka Integration Sofa', json_decode((string) $row->payload, true)['name']);

        $vectors = $this->app->make(QdrantProductVectorRepository::class);
        $query = (new FixedVectorLlmClient)->embed("Kafka Integration Sofa\nIndexed from Kafka integration test");
        $hits = $vectors->searchSimilar(1, $query, 5);

        $this->assertNotEmpty($hits);
        $this->assertSame($productId, $hits[0]['product_id']);
    }

    private function publishKafkaMessage(string $brokers, string $topic, string $payload): void
    {
        $config = new ProducerConfig;
        $config->setBroker($brokers);
        $config->setUpdateBrokers(true);

        $producer = new Producer($config);
        $producer->send($topic, $payload, 'recommendation-it-'.uniqid());
    }

    /**
     * @param  list<string>  $topics
     */
    private function consumeOneMessage(string $brokers, array $topics, callable $handler): void
    {
        $config = new ConsumerConfig;
        $config->setBroker($brokers);
        $config->setUpdateBrokers(true);
        $config->setGroupId('recommendation-kafka-it-'.uniqid('', true));
        $config->setClientId('recommendation-kafka-it');
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

    private function skipUnlessPostgresReachable(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('PostgreSQL is not reachable: '.$exception->getMessage());
        }
    }
}
