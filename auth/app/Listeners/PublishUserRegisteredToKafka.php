<?php

namespace App\Listeners;

use App\Contracts\KafkaPublisherInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishUserRegisteredToKafka
{
    public function __construct(
        private readonly KafkaPublisherInterface $publisher,
    ) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $topic = (string) config('kafka.user_registered_topic');

        try {
            $this->publisher->publish($topic, [
                'event' => 'user.registered',
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Failed to publish user.registered', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
