<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\Category;

class CategoryObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function created(Category $category): void
    {
        $this->kafka->publish('category.created', $this->body($category));
    }

    public function updated(Category $category): void
    {
        $this->kafka->publish('category.updated', $this->body($category));
    }

    public function deleted(Category $category): void
    {
        $this->kafka->publish('category.deleted', [
            'id' => $category->id,
            'store_id' => $category->store_id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function body(Category $category): array
    {
        return [
            'id' => $category->id,
            'store_id' => $category->store_id,
            'parent_id' => $category->parent_id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];
    }
}
