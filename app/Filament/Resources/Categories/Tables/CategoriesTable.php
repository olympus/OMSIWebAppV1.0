<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Models\User;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoryMultiSheetImport;
use App\Exports\CategoryMultiSheetExport;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => Category::query()->whereNull('parent_id')->whereNull('child_id'))
            ->columns([
                TextColumn::make('categories_name')
                    ->searchable()
                    ->label('Name'),


                TextColumn::make('slug')
                    ->searchable()
                    ->label('Slug'),

                // ImageColumn::make('categories_image')
                //    ->label('Image'),
                
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

                
                TextColumn::make('orderby')
                    ->numeric()
                    ->sortable()
                    ->label('Order By'),
                
                // TextColumn::make('created_by')
                //     ->label('Created By')
                //     ->formatStateUsing(fn ($state) => optional(User::find($state))->name ?? '-')
                //     ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable(),
                
                // TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable(),
            ])
            ->filters([
                //TrashedFilter::make(),
            ])
            ->defaultSort('id','desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Category')
                    ->modalDescription('If you delete this category, all related sub categories and linked product relations will also be deleted. Do you want to continue?')
                    ->modalSubmitActionLabel('Yes, Delete Everything')
                    ->successNotificationTitle('Category and related data deleted successfully'),

                //DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filename = 'categories_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                        Excel::store(new CategoryMultiSheetExport($data['start_date'], $data['end_date']), $filename, 'public');
                        return response()->download(storage_path('app/public/' . $filename))->deleteFileAfterSend(true);
                    }),
                Action::make('import')
                    ->label('Import from Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->form([
                        FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/public/' . $data['file']);

                        $import = new CategoryMultiSheetImport();

                        Excel::import($import, $filePath);

                        // 🔥 All errors
                        $errors = $import->getAllErrors();
 

                        // Notification::make()
                        //     ->title('Import completed')
                        //     ->success()
                        //     ->send();
                        Notification::make()
                            ->title('Import Completed')
                            ->body(empty($errors) 
                                ? 'All records imported successfully ✅' 
                                : implode("\n", $errors)
                            )
                            ->success()
                            ->send();
                    }),
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
