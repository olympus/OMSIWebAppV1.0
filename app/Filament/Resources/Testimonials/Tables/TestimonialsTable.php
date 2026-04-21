<?php

namespace App\Filament\Resources\Testimonials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                // TextColumn::make('designation')
                //     ->searchable(),
                
                TextColumn::make('type')
                    ->badge(),
                

                TextColumn::make('video_type')
                    ->badge(),
                    
                ImageColumn::make('thumbnail_image'),
                
                // TextColumn::make('video_url')
                //     ->searchable(),
                
                // TextColumn::make('video_file')
                //     ->searchable(),
                ToggleColumn::make('is_trending')
                    ->label('Is Trending')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),

                ToggleColumn::make('status')
                    ->label('Status')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),

                
                TextColumn::make('order_by')
                    ->numeric()
                    ->sortable()
                    ->label('Order By'),

                // TextColumn::make('order_by')
                //     ->numeric()
                //     ->sortable(),

                // IconColumn::make('status')
                //     ->boolean(),
                
                // IconColumn::make('is_trending')
                //     ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                
                // TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //TrashedFilter::make(),
            ])
            ->defaultSort('order_by', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]) 
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
