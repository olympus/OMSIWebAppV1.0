<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VideoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(function ($record) {
            $components = [
                TextEntry::make('title')->label('Title'),
                TextEntry::make('url')->label('Video URL'),
                TextEntry::make('description')->label('Description')->columnSpanFull(),
                TextEntry::make('nt_title')->label('Notification Title'),
                TextEntry::make('nt_description')->label('Notification Description'),
                IconEntry::make('enabled')->label('Enabled')->boolean(),
                TextEntry::make('created_at')->label('Created At')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->label('Updated At')->dateTime()->placeholder('-'),
            ];

            // 🧍 Show Watched By Table (infolist view only)
            $routeName = request()->route()?->getName();

            if ($routeName && str_contains($routeName, 'videos.view')) {
                $components[] = TextEntry::make('watched_by')
                    ->label('Watched By')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $customers = $record->relationLoaded('customers')
                            ? $record->customers
                            : $record->customers()->get();

                        $count = $customers->count();

                        if ($count === 0) {
                            return '<p>No views yet.</p>';
                        }

                        // 🧾 Build HTML table
                        $html = '
                            <h3 style="margin-top:10px;">Watched By (' . $count . ' views)</h3>
                            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                                <thead>
                                    <tr style="background-color:#f5f5f5; text-align:left;">
                                        <th style="padding:8px; border:1px solid #ddd;">ID</th>
                                        <th style="padding:8px; border:1px solid #ddd;">Name</th>
                                        <th style="padding:8px; border:1px solid #ddd;">Email</th>
                                        <th style="padding:8px; border:1px solid #ddd;">Watched At</th>
                                    </tr>
                                </thead>
                                <tbody>
                        ';

                        foreach ($customers as $customer) {
                            $watchedAt = $customer->pivot->created_at ?? '-';
                            $customerUrl = url('/admin/customers/' . $customer->id);

                            $html .= '
                                <tr>
                                    <td style="padding:8px; border:1px solid #ddd;">
                                        <a href="' . e($customerUrl) . '" style="color:#007bff; text-decoration:none;">' . e($customer->id) . '</a>
                                    </td>
                                    <td style="padding:8px; border:1px solid #ddd;">' . e(trim($customer->first_name . ' ' . ($customer->last_name ?? ''))) . '</td>
                                    <td style="padding:8px; border:1px solid #ddd;">' . e($customer->email) . '</td>
                                    <td style="padding:8px; border:1px solid #ddd;">' . e($watchedAt) . '</td>
                                </tr>
                            ';
                        }

                        $html .= '
                                </tbody>
                            </table>
                        ';

                        return $html;
                    })
                    ->columnSpanFull();
            }

            // ✅ Must return $components so Filament can render them
            return $components;
        });
    }
}
