<?php

namespace App\Console\Commands;

use App\Services\CatalogIndexIngestService;
use Illuminate\Console\Command;
use JsonException;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use Throwable;

class ConsumeCatalogIndexCommand extends Command
{
    protected $signature = 'kafka:consume-catalog-index';

    protected $description = 'Consume Catalog product facts for the local search index (stub ingest)';

    public function handle(CatalogIndexIngestService $ingest): int
    {
        $config = new ConsumerConfig;
        $config->setBroker((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setGroupId((string) config('kafka.consumer_group'));
        $config->setGroupInstanceId((string) config('kafka.consumer_group').'-1');
        $config->setClientId('recommendation-service');
        $config->setTopic(config('kafka.consume_topics'));
        $config->setInterval(0.5);

        $topics = config('kafka.consume_topics');
        $this->info('Listening: '.implode(', ', $topics));

        $consumer = new Consumer($config, function (ConsumeMessage $message) use ($ingest): void {
            $this->process($ingest, $message);
        });

        $consumer->start();

        return self::SUCCESS;
    }

    private function process(CatalogIndexIngestService $ingest, ConsumeMessage $message): void
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
