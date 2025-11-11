<?php

namespace App\Filament\Resources\Promailers\Tables;

use App\Models\Promailer;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class PromailersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()->wrap()->sortable(),
                TextColumn::make('abbreviation')
                    ->searchable()->wrap()->sortable(), 
                ImageColumn::make('frontimage')
                    ->label('FrontImage')
                    ->getStateUsing(fn ($record) => $record->image ? asset('storage/' . $record->image) : null)
                    ->searchable(false),
                
                IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')   // Show X when disabled = 1
                    ->falseIcon('heroicon-o-check-circle') // Show check when disabled = 0
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggleStatus')
                    ->label(fn ($record) => $record->status == 1 ? 'Published' : 'Unpublished')
                    ->color(fn ($record) => $record->status == 1 ? 'success' : 'danger')
                    ->icon(fn ($record) => $record->status == 1 ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->action(function (Action $action, $record) {
                        // Fetch a fresh model instance to be safe
                        $model = Promailer::find($record->id);

                        $model->status = $model->status == 1 ? 0 : 1;
                        $model->save();

                        // Notify user
                        Notification::make()
                            ->title('Status updated')
                            ->success()
                            ->send();

                        // Optionally refresh the table row UI
                        $action->record($model);
                    })
                    ->tooltip(fn ($record) => $record->status == 1 ? 'Click to Unpublish' : 'Click to Publish')
            ]);
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            // ]);
    }
}
