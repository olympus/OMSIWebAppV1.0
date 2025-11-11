<?php

namespace App\Filament\Resources\Videos\Tables;

use App\Models\Videos;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()->sortable(),

                // TextColumn::make('url')
                //     ->label('URL')
                //     ->url(fn ($record) => $record->url, shouldOpenInNewTab: true) // ✅ correct way in Filament 4
                //     ->searchable()
                //     ->sortable()
                //     ->copyable()
                //     ->copyMessage('URL copied!')
                //     ->copyMessageDuration(1500),

                TextColumn::make('url')
                    ->label('URL')
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->sortable(query: function ($query, string $direction) {
                        $query->orderBy('url', $direction);
                    })
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()->sortable(),

                // TextColumn::make('nt_description')
                //     ->label('Description')
                //     ->searchable()->sortable(),

                IconColumn::make('enabled')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Status'),
                
                TextColumn::make('views')
                ->label('Views')
                ->getStateUsing(fn ($record) => $record->customers?->count() ?? 0),


                TextColumn::make('created_at')
                    ->dateTime('d M Y h:i a')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime('d M Y h:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // (optional filters can be added later)
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // ✅ Toggle Enable/Disable Action
                Action::make('toggleEnabled')
                    ->label(fn ($record) => $record->enabled ? 'Disable' : 'Enable')
                    ->color(fn ($record) => $record->enabled ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->enabled ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(function ($record) {
                        $record->enabled = !$record->enabled;
                        $record->save();
                    }),

                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

                // ✅ Export All Data Button
                Action::make('export_all')
                    ->label('Export All Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (): StreamedResponse {
                        $videos = \App\Models\Video::all(); // export all records

                        $filename = 'videos_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"$filename\"",
                        ];

                        $callback = function () use ($videos) {
                            $handle = fopen('php://output', 'w');

                            // CSV Header
                            fputcsv($handle, [
                                'ID',
                                'Title',
                                'URL',
                                'Description',
                                'Notification Title',
                                'Notification Description',
                                'Enabled',
                                'Created At',
                                'Updated At',
                                'Views',
                                'Viewed_at',
                                'Customer_id',
                                'Customer_name',
                                'Email',
                                'Mobile Number',

                            ]);

                            // Data Rows
                            foreach ($videos as $video) {
                                foreach($video->customers as $customer){
                                    fputcsv($handle, [
                                        $video->id,
                                        $video->title,
                                        $video->url,
                                        $video->description,
                                        $video->nt_title,
                                        $video->nt_description,
                                        $video->enabled ? 'Yes' : 'No',
                                        $video->created_at,
                                        $video->updated_at,
                                        $video->customers->count(),
                                        '1',
                                        $customer->pivot->created_at,
                                        $customer->id,
                                        $customer->first_name,
                                        $customer->email,
                                        $customer->mobile_number,

                                    ]);
                                }
                                
                            }

                            fclose($handle);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ]);
    }
}
