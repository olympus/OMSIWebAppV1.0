<?php

namespace App\Filament\Resources\ArchiveServiceRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArchiveServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cvm_id')
                    ->searchable(),
                TextColumn::make('import_id')
                    ->searchable(),
                TextColumn::make('request_type')
                    ->searchable(),
                TextColumn::make('sub_type')
                    ->searchable(),
                TextColumn::make('customer_id')
                    ->searchable(),
                TextColumn::make('hospital_id')
                    ->searchable(),
                TextColumn::make('dept_id')
                    ->searchable(),
                TextColumn::make('sap_id')
                    ->searchable(),
                TextColumn::make('sfdc_id')
                    ->searchable(),
                TextColumn::make('sfdc_customer_id')
                    ->searchable(),
                TextColumn::make('product_category')
                    ->searchable(),
                TextColumn::make('employee_code')
                    ->searchable(),
                TextColumn::make('last_updated_by')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('is_escalated')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('escalation_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('escalation_assign1')
                    ->searchable(),
                TextColumn::make('escalation_assign2')
                    ->searchable(),
                TextColumn::make('escalation_assign3')
                    ->searchable(),
                TextColumn::make('escalation_assign4')
                    ->searchable(),
                TextColumn::make('escalated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('feedback_id')
                    ->searchable(),
                TextColumn::make('feedback_requested')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_practice')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('acknowledgement_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('acknowledgement_status')
                    ->searchable(),
                TextColumn::make('reminder_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('happy_code')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('happy_code_delivered_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('acknowledged_by')
                    ->searchable(),
                TextColumn::make('is_happy_code')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
