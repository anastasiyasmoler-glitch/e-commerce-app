<?php

namespace App\Console\Commands;

use App\Services\NotificationDispatchService;
use Illuminate\Console\Command;
use JsonException;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use Throwable;

class ConsumeNotificationsCommand extends Command
{
    protected $signature = 'kafka:consume-notifications';

    protected $description = 'Consume Kafka notification events and send email';

    public function handle(NotificationDispatchService $dispatcher): int
    {
        $config = new ConsumerConfig;
        $config->setBroker((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setGroupId((string) config('kafka.consumer_group'));
        $config->setGroupInstanceId((string) config('kafka.consumer_group').'-1');
        $config->setClientId('notification-service');
        $config->setTopic(config('kafka.topics'));
        $config->setInterval(0.5);

        $this->info('Listening on Kafka topics: '.implode(', ', config('kafka.topics')));

        $consumer = new Consumer($config, function (ConsumeMessage $message) use ($dispatcher): void {
            $this->process($dispatcher, $message);
        });

        $consumer->start();

        return self::SUCCESS;
    }

    private function process(NotificationDispatchService $dispatcher, ConsumeMessage $message): void
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

        $recipient = (string) ($payload['email'] ?? $payload['recipient'] ?? '');

        if ($recipient === '') {
            $this->warn('Skip message without email on '.$message->getTopic());

            return;
        }

        try {
            $dispatcher->dispatch($message->getTopic(), $recipient, $payload);
            $this->info('Handled '.$message->getTopic().' for '.$recipient);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
        }
    }
}
