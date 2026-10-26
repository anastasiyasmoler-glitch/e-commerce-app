<?php

namespace Tests\Unit;

use App\Contracts\KafkaPublisherInterface;
use App\Kafka\ArrayKafkaPublisher;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPublishKafkaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $kafka = new ArrayKafkaPublisher;
        $this->app->instance(ArrayKafkaPublisher::class, $kafka);
        $this->app->instance(KafkaPublisherInterface::class, $kafka);
        $this->kafka = $kafka;
    }

    private ArrayKafkaPublisher $kafka;

    public function test_publish_toggle_emits_published_and_unpublished_events(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $category = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Draft Sofa',
            'slug' => 'draft-sofa',
            'price' => 1000,
            'is_published' => false,
        ]);

        $this->kafka->messages = [];

        $product->update(['is_published' => true]);

        $topics = array_column($this->kafka->messages, 'topic');
        $this->assertContains('product.published', $topics);
        $this->assertContains('product.updated', $topics);

        $this->kafka->messages = [];

        $product->update(['name' => 'Published Sofa']);

        $topics = array_column($this->kafka->messages, 'topic');
        $this->assertSame(['product.updated'], $topics);

        $this->kafka->messages = [];

        $product->update(['is_published' => false]);

        $topics = array_column($this->kafka->messages, 'topic');
        $this->assertContains('product.unpublished', $topics);
        $this->assertContains('product.updated', $topics);
    }

    public function test_event_payload_includes_publish_and_stock_flags(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $category = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Sofa',
            'slug' => 'sofa',
            'price' => 1000,
            'is_published' => true,
        ]);

        $payload = $product->toEventPayload();
        $this->assertTrue($payload['is_published']);
        $this->assertFalse($payload['in_stock']);
    }
}
