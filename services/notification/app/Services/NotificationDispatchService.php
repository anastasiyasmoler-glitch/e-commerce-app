<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderPaidMail;
use App\Mail\UserRegisteredMail;
use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use InvalidArgumentException;
use Throwable;

class NotificationDispatchService
{
    public function __construct(
        private NotificationLogRepositoryInterface $logs,
        private Mailer $mailer,
        private KafkaPublisherInterface $kafka,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $event, string $recipient, array $payload): NotificationLog
    {
        if (! in_array($event, config('kafka.topics'), true)) {
            throw new InvalidArgumentException("Unknown notification event [{$event}].");
        }

        $log = $this->logs->createPending($event, 'email', $recipient, $payload);
        $maxAttempts = max(1, (int) config('kafka.max_attempts'));

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $mailable = $this->mailableFor($event, $payload);
                $mailable->to($recipient);
                $this->mailer->send($mailable);

                return $this->logs->markSent($log);
            } catch (Throwable $exception) {
                $log = $this->logs->markFailed($log, $exception->getMessage());

                if ($attempt === $maxAttempts) {
                    $this->kafka->publish((string) config('kafka.dlq_topic'), [
                        'event' => $event,
                        'recipient' => $recipient,
                        'payload' => $payload,
                        'notification_log_id' => (string) $log->getKey(),
                    ]);

                    return $this->logs->markDlq($log);
                }

                usleep($this->backoffMicroseconds($attempt));
            }
        }

        return $log;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mailableFor(string $event, array $payload): Mailable
    {
        return match ($event) {
            'user.registered' => new UserRegisteredMail($payload),
            'order.paid' => new OrderPaidMail($payload),
            'order.confirmed' => new OrderConfirmedMail($payload),
            'order.cancelled' => new OrderCancelledMail($payload),
            default => throw new InvalidArgumentException("Unknown notification event [{$event}]."),
        };
    }

    private function backoffMicroseconds(int $failedAttempt): int
    {
        $baseMs = max(0, (int) config('kafka.backoff_base_ms'));

        return $baseMs * (2 ** ($failedAttempt - 1)) * 1000;
    }
}
