<?php

namespace App\Console\Commands;

use App\Services\OutboxPublisherService;
use Illuminate\Console\Command;
use Throwable;

class PublishOutboxCommand extends Command
{
    protected $signature = 'kafka:publish-outbox {--once : Flush pending rows once and exit}';

    protected $description = 'Publish pending outbox messages to Kafka';

    public function handle(OutboxPublisherService $outbox): int
    {
        if ($this->option('once')) {
            $sent = $outbox->flush();
            $this->info("Published {$sent} outbox message(s).");

            return self::SUCCESS;
        }

        $this->info('Publishing catalog outbox to Kafka.');

        while (true) {
            try {
                $outbox->flush();
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());
            }

            usleep(500_000);
        }
    }
}
