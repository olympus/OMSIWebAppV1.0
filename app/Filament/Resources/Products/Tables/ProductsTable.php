<?php

namespace App\Filament\Resources\Products\Tables;

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
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\ProductMultiSheetExport;
use App\Imports\ProductMultiSheetImport; 

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('product_name')
                    ->searchable()->label('Name'),
                
                TextColumn::make('product_sku')
                    ->searchable()->label('SKU'),  
                
                // TextColumn::make('category.categories_name')
                //     ->label('Category')
                //     ->searchable(),
                    
                //ImageColumn::make('product_image'),
                
                ToggleColumn::make('is_new')
                    ->label('Is New')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),

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

                
                // TextColumn::make('orderby')
                //     ->numeric()
                //     ->sortable()
                //     ->label('Order By'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                
            ])
            ->filters([
                //
            ])
            
            ->defaultSort('id','desc')

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
                        $filename = 'products_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                        Excel::store(new ProductMultiSheetExport($data['start_date'], $data['end_date']), $filename, 'public');
                        return response()->download(storage_path('app/public/' . $filename))->deleteFileAfterSend(true);
                    }),

                Action::make('import')
                    ->label('Import from Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->form([
                        FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel'
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {

                        $filePath = storage_path('app/public/' . $data['file']);

                        \Maatwebsite\Excel\Facades\Excel::import(
                            new \App\Imports\ProductMultiSheetImport(),
                            $filePath
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Import completed successfully')
                            ->success()
                            ->send();
                    }),

                /*Action::make('import')
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
                        Excel::import(new ProductMultiSheetImport(), $filePath);

                        Notification::make()
                            ->title('Import completed')
                            ->success()
                            ->send();
                    }),*/
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
