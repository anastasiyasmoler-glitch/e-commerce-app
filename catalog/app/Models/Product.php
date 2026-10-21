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
#[Fillable(['store_id', 'category_id', 'name', 'slug', 'description', 'sku', 'price', 'image'])]
class Product extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
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
        ];
    }
}
