<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Stock|null $stock
 */
#[Fillable(['store_id', 'category_id', 'name', 'slug', 'description', 'sku', 'price', 'attributes', 'image'])]
class Product extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'attributes' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }
}
