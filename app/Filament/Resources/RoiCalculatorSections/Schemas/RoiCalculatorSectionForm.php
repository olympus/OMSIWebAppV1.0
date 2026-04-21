<?php

namespace App\Filament\Resources\RoiCalculatorSections\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class RoiCalculatorSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Video Type
                |--------------------------------------------------------------------------
                */

                Select::make('video_type')
                    ->label('Video Type')
                    ->options([
                        'youtube' => 'Youtube Video',
                        'other' => 'Upload Video',
                    ])
                    ->required()
                    ->default('youtube')
                    ->reactive()
                    ->afterStateUpdated(function ($set) {

                        $set('video_url', null);
                        $set('video_file', null);

                    }),

                /*
                |--------------------------------------------------------------------------
                | Video URL
                |--------------------------------------------------------------------------
                */

                TextInput::make('video_url')
                    ->label('Youtube Video URL')

                    ->visible(fn ($get) =>
                        $get('video_type') === 'youtube'
                    )

                    ->required(fn ($get) =>
                        $get('video_type') === 'youtube'
                    )

                    ->rule('regex:/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/')

                    ->validationMessages([
                        'required' => 'Youtube URL is required.',
                        'regex' => 'Enter valid Youtube URL.',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Video File
                |--------------------------------------------------------------------------
                */

                FileUpload::make('video_file')
                    ->label('Upload Video')

                    ->disk('public')

                    ->visibility('public')

                    ->directory('roi-calculator/video/' . date('FY'))

                    ->enableDownload()

                    ->visible(fn ($get) =>
                        $get('video_type') === 'other'
                    )

                    ->required(fn ($get) =>
                        $get('video_type') === 'other'
                    )

                    ->maxSize(51200)

                    ->validationMessages([
                        'required' => 'Video file is required.',
                        'max' => 'Video must not exceed 50MB.',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Thumbnail
                |--------------------------------------------------------------------------
                */

                FileUpload::make('thumbnail')
                    ->label('Thumbnail Image')

                    ->disk('public')

                    ->visibility('public')

                    ->directory('roi-calculator/thumbnail/' . date('FY'))

                    ->enableDownload()

                    ->image()

                    ->required()

                    ->maxSize(2048)

                    ->validationMessages([
                        'required' => 'Thumbnail is required.',
                        'max' => 'Image must not exceed 2MB.',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                Toggle::make('status')
                    ->label('Status')
                    ->default(true)
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

            ]);
    }
}
