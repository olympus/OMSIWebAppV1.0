<?php

namespace App\Filament\Resources\Users\Tables;

use App\Role;
use App\RoleUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles')
                    ->label('Roles')
                    ->getStateUsing(function ($record) {
                        // Fetch role IDs assigned to the user
                        $roles = DB::table('model_has_roles')->where('model_id', $record->id)->pluck('role_id')->toArray();
                        // Get role names
                        $role_list = DB::table('roles')->whereIn('id', $roles)->pluck('name')->toArray();
                        // Return comma-separated string
                        return implode(', ', $role_list) ?: '-';
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                // IconColumn::make('is_expired')
                //     ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
