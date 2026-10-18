<?php

namespace App\Console\Commands;

use App\Services\NotificationDispatchService;
use Illuminate\Console\Command;
use JsonException;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use Throwable;

class ConsumeNotificationDlqCommand extends Command
{
    protected $signature = 'kafka:consume-notifications-dlq';

    protected $description = 'Consume notifications.dlq and replay failed email deliveries once';

    public function handle(NotificationDispatchService $dispatcher): int
    {
        $topic = (string) config('kafka.dlq_topic');

        $config = new ConsumerConfig;
        $config->setBroker((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setGroupId((string) config('kafka.consumer_group').'-dlq');
        $config->setGroupInstanceId((string) config('kafka.consumer_group').'-dlq-1');
        $config->setClientId('notification-dlq');
        $config->setTopic([$topic]);
        $config->setInterval(0.5);

        $this->info('Listening on DLQ topic: '.$topic);

        $consumer = new Consumer($config, function (ConsumeMessage $message) use ($dispatcher): void {
            $this->process($dispatcher, $message);
        });

        $consumer->start();

        return self::SUCCESS;
    }

    private function process(NotificationDispatchService $dispatcher, ConsumeMessage $message): void
    {
        try {
            $body = json_decode((string) $message->getValue(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Skip invalid DLQ JSON: '.$exception->getMessage());

            return;
        }

        if (! is_array($body)) {
            $this->error('Skip non-object DLQ payload.');

            return;
        }

        try {
            $dispatcher->replayFromDlq($body);
            $this->info('Replayed DLQ message for event '.($body['event'] ?? 'unknown'));
        } catch (Throwable $exception) {
            $this->error('DLQ replay failed: '.$exception->getMessage());
        }
    }
}
