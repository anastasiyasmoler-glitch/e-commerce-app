<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'store_id', 'name', 'sku', 'price_cents'])]
class ProductSnapshot extends Model
{
    protected $primaryKey = 'product_id';

    public $incrementing = false;
}
