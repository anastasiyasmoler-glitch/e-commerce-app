<?php

namespace App\Console\Commands;

use App\Services\OrderKafkaIngestService;
use Illuminate\Console\Command;
use JsonException;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use Throwable;

class ConsumeOrdersCommand extends Command
{
    protected $signature = 'kafka:consume-orders';

    protected $description = 'Consume catalog product events and stock saga replies for Order';

    public function handle(OrderKafkaIngestService $ingest): int
    {
        $config = new ConsumerConfig;
        $config->setBroker((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setGroupId((string) config('kafka.consumer_group'));
        $config->setGroupInstanceId((string) config('kafka.consumer_group').'-1');
        $config->setClientId('order-service');
        $config->setTopic(config('kafka.consume_topics'));
        $config->setInterval(0.5);

        $this->info('Listening: '.implode(', ', config('kafka.consume_topics')));

        $consumer = new Consumer($config, function (ConsumeMessage $message) use ($ingest): void {
            $this->process($ingest, $message);
        });

        $consumer->start();

        return self::SUCCESS;
    }

    private function process(OrderKafkaIngestService $ingest, ConsumeMessage $message): void
    {
        try {
            $payload = json_decode((string) $message->getValue(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Skip invalid JSON: '.$exception->getMessage());

            return;
        }

        if (! is_array($payload)) {
            $this->error('Skip non-object Kafka payload.');

            return;
        }

        try {
            $ingest->handle($message->getTopic(), $payload);
            $this->info('Handled '.$message->getTopic());
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
        }
    }
}
