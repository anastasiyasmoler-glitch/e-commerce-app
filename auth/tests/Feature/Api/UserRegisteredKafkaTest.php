<?php

namespace Tests\Feature\Api;

use App\Kafka\ArrayKafkaPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegisteredKafkaTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_register_publishes_user_registered_payload(): void
    {
        $publisher = $this->app->make(ArrayKafkaPublisher::class);

        $this->postJson('/api/register', [
            'name' => 'Kafka User',
            'email' => 'kafka-user@example.com',
            'phone' => '375293333333',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $this->assertCount(1, $publisher->messages);
        $this->assertSame('user.registered', $publisher->messages[0]['topic']);
        $this->assertSame('user.registered', $publisher->messages[0]['body']['event']);
        $this->assertSame('kafka-user@example.com', $publisher->messages[0]['body']['email']);
        $this->assertSame('Kafka User', $publisher->messages[0]['body']['name']);
        $this->assertArrayHasKey('user_id', $publisher->messages[0]['body']);
    }

    public function test_login_does_not_publish_user_registered(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Once',
            'email' => 'once@example.com',
            'phone' => '375294444444',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $publisher = $this->app->make(ArrayKafkaPublisher::class);
        $publisher->messages = [];

        $this->postJson('/api/login', [
            'email' => 'once@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertSame([], $publisher->messages);
    }
}
