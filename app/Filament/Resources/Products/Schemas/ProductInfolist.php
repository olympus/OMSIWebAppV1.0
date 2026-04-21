<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Information')
                ->columns(3)
                ->schema([

                    TextEntry::make('product_name')
                        ->label('Product Name')
                        ->weight('bold')
                        ->placeholder('-'),

                    TextEntry::make('slug')
                        ->copyable()
                        ->badge()
                        ->color('gray')
                        ->placeholder('-'),

                    TextEntry::make('product_sku')
                        ->label('SKU')
                        ->badge()
                        ->color('info')
                        ->placeholder('-'),

                ]),


            Section::make('Product Image')
                ->columns(2)
                ->schema([

                    ImageEntry::make('product_image')
                        ->disk('public')
                        ->height(120)
                        ->width(120)
                        ->placeholder('No Image'),

                    TextEntry::make('product_image_url')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('-'),

                ]),


            Section::make('Short Description')->collapsed()
                ->schema([

                    TextEntry::make('short_description')
                        ->html()
                        ->columnSpanFull(), 
                ]),

            Section::make('Long Description')->collapsed()
                ->schema([ 
                    TextEntry::make('long_description')
                        ->html()
                        ->columnSpanFull(),

                ]),


            Section::make('Status')
                ->columns(5)
                ->schema([

                    IconEntry::make('status')->boolean(),

                    IconEntry::make('is_new')->boolean(),

                    IconEntry::make('is_trending')->boolean(),

                    IconEntry::make('latest_product_show_in_popup')->boolean(),

                    IconEntry::make('is_notify')->boolean(),

                ]),


            Section::make('Audit')
                ->columns(2)
                ->schema([

                    TextEntry::make('created_at')
                        ->dateTime('d M Y, h:i A'),

                    TextEntry::make('updated_at')
                        ->dateTime('d M Y, h:i A'),

                ]),

        ]);
    }
}
