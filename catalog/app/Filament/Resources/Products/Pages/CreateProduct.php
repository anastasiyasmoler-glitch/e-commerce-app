<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['stock_quantity'], $data['stock_reserved']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Product $product */
        $product = $this->record;
        $raw = $this->form->getRawState();

        Stock::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'store_id' => $product->store_id,
            ],
            [
                'quantity' => (int) ($raw['stock_quantity'] ?? 0),
                'reserved' => (int) ($raw['stock_reserved'] ?? 0),
            ],
        );
    }
}
