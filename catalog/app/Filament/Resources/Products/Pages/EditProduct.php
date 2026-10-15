<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\Stock;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Product $product */
        $product = $this->record;
        $stock = $product->stock;

        $data['stock_quantity'] = $stock?->quantity ?? 0;
        $data['stock_reserved'] = $stock?->reserved ?? 0;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['stock_quantity'], $data['stock_reserved']);

        return $data;
    }

    protected function afterSave(): void
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
