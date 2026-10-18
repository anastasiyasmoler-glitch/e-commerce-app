<?php

namespace Tests\Unit;

use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\LonglangKafkaPublisher;
use App\Kafka\OutboxKafkaPublisher;
use App\Models\OutboxMessage;
use App\Repositories\EloquentOutboxMessageRepository;
use App\Services\OutboxPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutboxPublisherServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_publisher_writes_rows_instead_of_kafka(): void
    {
        $publisher = new OutboxKafkaPublisher(new EloquentOutboxMessageRepository);
        $publisher->publish('product.created', ['id' => 7]);

        $this->assertDatabaseHas('outbox_messages', [
            'topic' => 'product.created',
            'published_at' => null,
        ]);
    }

    public function test_flush_marks_messages_published_after_broker_send(): void
    {
        $broker = new ArrayKafkaPublisher;
        OutboxMessage::query()->create([
            'topic' => 'stock.changed',
            'payload' => ['product_id' => 1],
        ]);

        $service = new OutboxPublisherService(
            new EloquentOutboxMessageRepository,
            $this->fakeBroker($broker),
        );

        $this->assertSame(1, $service->flush());
        $this->assertNotNull(OutboxMessage::query()->first()?->published_at);
        $this->assertSame('stock.changed', $broker->messages[0]['topic']);
    }

    private function fakeBroker(ArrayKafkaPublisher $array): LonglangKafkaPublisher
    {
        return new class($array) extends LonglangKafkaPublisher
        {
            public function __construct(private readonly ArrayKafkaPublisher $inner)
            {
                // Skip parent Kafka wiring in tests.
            }

            public function publish(string $topic, array $body): void
            {
                $this->inner->publish($topic, $body);
            }
        };
    }
}
