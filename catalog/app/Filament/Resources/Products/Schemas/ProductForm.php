<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Price in the smallest currency unit (cents).'),
                KeyValue::make('attributes')
                    ->keyLabel('Name')
                    ->valueLabel('Value')
                    ->reorderable(),
                FileUpload::make('image')
                    ->image()
                    ->disk('s3')
                    ->directory('products')
                    ->visibility('public'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Fieldset::make('Stock')
                    ->relationship('stock')
                    ->schema([
                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('reserved')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                        $data['store_id'] = (int) $get('store_id');

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $get): array {
                        $data['store_id'] = (int) $get('store_id');

                        return $data;
                    }),
            ]);
    }
}
