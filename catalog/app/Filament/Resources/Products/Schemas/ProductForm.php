<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
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
                Toggle::make('is_published')
                    ->label('Published on storefront')
                    ->default(false)
                    ->helperText('Unpublished products are hidden from the public catalog and recommendation index events.'),
                Repeater::make('attributeValues')
                    ->relationship('attributeValues')
                    ->schema([
                        Select::make('attribute_id')
                            ->relationship(
                                name: 'attribute',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, $get) => $query->where('store_id', (int) $get('../../store_id')),
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('value')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
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
