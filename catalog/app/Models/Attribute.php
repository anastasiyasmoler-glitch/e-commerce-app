<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['store_id', 'code', 'name'])]
class Attribute extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
