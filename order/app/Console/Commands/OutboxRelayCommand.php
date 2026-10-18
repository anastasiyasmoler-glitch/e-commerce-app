<?php

namespace App\Console\Commands;

use App\Services\OutboxPublisherService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class OutboxRelayCommand extends Command
{
    protected $signature = 'order:outbox-relay {--once : Flush pending rows once and exit}';

    protected $description = 'Publish pending order outbox messages to Kafka (at-least-once delivery)';

    public function handle(OutboxPublisherService $relay): int
    {
        $workerId = 'order-outbox:'.gethostname().':'.Str::uuid()->toString();

        if ($this->option('once')) {
            $sent = $relay->flush($workerId);
            $this->info("Published {$sent} outbox message(s).");

            return self::SUCCESS;
        }

        $this->info('Relaying order outbox to Kafka.');

        while (config('kafka.outbox_relay_enabled')) {
            $sent = $relay->flush($workerId);

            if ($sent === 0) {
                sleep(1);
            }
        }

        return self::SUCCESS;
    }
}
