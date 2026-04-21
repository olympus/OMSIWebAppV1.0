<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Models\Testimonial;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TestimonialInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([


                TextEntry::make('product.product_name')
                    ->placeholder('-'),

                TextEntry::make('name')
                    ->placeholder('-'),

                TextEntry::make('designation')
                    ->placeholder('-'),

                TextEntry::make('type')
                    ->badge(),

                TextEntry::make('order_by')
                    ->numeric()
                    ->placeholder('-'),

                IconEntry::make('status')
                    ->boolean(),

                IconEntry::make('is_trending')
                    ->boolean(),

                // Show only if TEXT type
                TextEntry::make('message')
                    ->columnSpanFull()
                    ->placeholder('-')
                    ->visible(fn (Testimonial $record) => $record->type === 'text'),

                // Thumbnail preview
                ImageEntry::make('full_thumbnail_image_url')
                    ->label('Thumbnail Image')
                    ->visible(fn (Testimonial $record) => filled($record->thumbnail_image))
                    ->columnSpanFull(),


                TextEntry::make('video_type')
                    ->badge(),

                // Show only if VIDEO type
                TextEntry::make('video_url')
                    ->label('Video URL')
                    ->url(fn ($record) => $record->video_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Testimonial $record) => 
                        $record->type === 'video' && filled($record->video_url)
                    ),

                TextEntry::make('full_video_url')
                    ->label('Uploaded Video')
                    ->url(fn ($record) => $record->full_video_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Testimonial $record) => 
                        $record->type === 'video' && filled($record->video_file)
                    ),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Testimonial $record): bool => $record->trashed()),
            ]);
    }
}
