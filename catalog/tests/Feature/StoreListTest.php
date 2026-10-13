<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_stores_ordered_by_name(): void
    {
        Store::query()->create(['name' => 'Warm Light', 'slug' => 'warm-light']);
        Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);

        $this->getJson('/stores')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Nordic Home')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }
}
