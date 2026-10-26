<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Stock|null $stock
 */
#[Fillable(['store_id', 'category_id', 'name', 'slug', 'description', 'sku', 'price', 'image', 'is_published'])]
class Product extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_published' => 'boolean',
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

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * @return array<string, string>
     */
    public function attributesMap(): array
    {
        $this->loadMissing('attributeValues.attribute');

        $map = [];
        foreach ($this->attributeValues as $row) {
            $code = $row->attribute?->code;
            if ($code === null || $code === '') {
                continue;
            }

            $map[$code] = $row->value;
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function toEventPayload(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'attributes' => $this->attributesMap(),
            'image' => $this->image,
            'is_published' => (bool) $this->is_published,
            'in_stock' => $this->isInStock(),
        ];
    }

    public function isInStock(): bool
    {
        $this->loadMissing('stock');
        $stock = $this->stock;
        if ($stock === null) {
            return false;
        }

        return max(0, (int) $stock->quantity - (int) $stock->reserved) > 0;
    }
}
