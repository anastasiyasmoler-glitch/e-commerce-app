<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Contracts\Mail\Mailer;
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
                $this->mailer->html(
                    $this->htmlBody($event, $payload),
                    function ($message) use ($recipient, $event): void {
                        $message->to($recipient)->subject($this->subject($event));
                    }
                );

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

    private function subject(string $event): string
    {
        return match ($event) {
            'user.registered' => 'Welcome',
            'order.paid' => 'Payment received',
            'order.confirmed' => 'Order confirmed',
            'order.cancelled' => 'Order cancelled',
            default => 'Notification',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function htmlBody(string $event, array $payload): string
    {
        $name = (string) ($payload['name'] ?? $payload['email'] ?? 'there');

        return match ($event) {
            'user.registered' => "<p>Welcome, {$this->e($name)}.</p>",
            'order.paid' => '<p>We received your payment.</p>',
            'order.confirmed' => '<p>Your order is confirmed.</p>',
            'order.cancelled' => '<p>Your order was cancelled.</p>',
            default => '<p>Notification</p>',
        };
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function backoffMicroseconds(int $failedAttempt): int
    {
        $baseMs = max(0, (int) config('kafka.backoff_base_ms'));

        return $baseMs * (2 ** ($failedAttempt - 1)) * 1000;
    }
}
