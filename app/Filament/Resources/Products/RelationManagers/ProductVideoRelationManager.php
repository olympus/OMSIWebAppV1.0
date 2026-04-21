<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;

class ProductVideoRelationManager extends RelationManager
{
    protected static string $relationship = 'productVideos';

    protected static ?string $recordTitleAttribute = 'video_title';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                //ImageColumn::make('video_thumbnail')->label('Thumbnail')->square(),
                TextColumn::make('video_title')->label('Title')->searchable()->sortable(),
                TextColumn::make('video_url')->label('URL')->searchable()->sortable(),
                ToggleColumn::make('status')->label('Status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // add filters if needed
            ])
            ->defaultSort('id','desc')
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    //->modalWidth('lg')
                    ->label('Create')
                    ->modalHeading('Create')
                    ->modalSubmitActionLabel('Create'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /*public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('video_title')
                    ->required()
                    ->label('Video Title'),

                
                FileUpload::make('video_thumbnail')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('product_video/' . date('FY'))
                    ->enableDownload()
                    ->image(),

                // TextInput::make('video_alt_text')
                //     ->label('Alt Text')
                //     ->nullable(),


                FileUpload::make('video_file')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('product_video/' . date('FY'))
                    ->enableDownload(),

                TextInput::make('video_url')
                    ->required()
                    ->url()
                    ->label('Video URL'),

                TextInput::make('orderby')
                    ->required() 
                    ->label('Order By'),
                
                Toggle::make('status')
                    ->label('Status')
                    ->default(true),
            ]);
    }*/

    /*public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('video_title')
                ->required()
                ->label('Video Title')
                ->maxLength(100)
                ->helperText('Maximum 100 characters are allowed.'),

            FileUpload::make('video_thumbnail')
                ->disk('public')
                ->visibility('public')
                ->required()
                ->directory('product_video/' . date('FY'))
                ->image()
                ->maxSize(300)
                ->enableDownload(),

            // ================= VIDEO FILE ================= //

            Select::make('video_type')
                ->label('Video Type')
                ->options([
                    'youtube_video' => 'Youtube Video',
                    'other_video' => 'Other Video',
                ]) 
                ->required()
                ->live(),

            FileUpload::make('video_file')
                ->disk('public')
                ->visibility('public')
                ->directory('product_video/' . date('FY'))
                ->acceptedFileTypes(['video/mp4'])
                ->maxSize(10240) // 10MB
                ->visible(fn ($get) => $get('video_type') === 'other_video')
                ->required(fn ($get) => $get('video_type') === 'other_video' && blank($get('video_url')))
                ->live()
                ->validationMessages([
                    'required' => 'Either Video URL or Video File is required.',
                    'max' => 'Video must not exceed 10MB.',
                ])
                ->enableDownload(),

            // ================= VIDEO URL ================= //

            TextInput::make('video_url')
                ->label('Video URL')
                ->url()
                ->visible(fn ($get) => filled($get('video_type')))
                ->required(fn ($get) => $get('video_type') === 'youtube_video' || ($get('video_type') === 'other_video' && blank($get('video_file'))))
                ->live()
                ->rules([
                    fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if ($get('video_type') === 'youtube_video' && ! str_contains($value, 'www.youtube.com')) {
                            $fail('The URL must be a YouTube link (www.youtube.com).');
                        }
                    },
                ])
                ->validationMessages([
                    'required' => 'Either Video URL or Video File is required.',
                    'url' => 'Please enter a valid URL.',
                ]),

            TextInput::make('orderby')
                ->label('Order')
                ->numeric()
                ->minValue(1)
                ->default(null)
                ->validationMessages([
                    'min_value' => 'Order must be greater than 0.',
                ]),   
                
            Toggle::make('status')
                ->label('Status')
                ->default(true),

            Hidden::make('created_by')
                ->default(fn ($record) => $record?->created_by ?? auth()->id()),
        ]);
    }*/

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('video_title')
                ->required()
                ->label('Video Title')
                ->maxLength(100)
                ->helperText('Maximum 100 characters are allowed.'),

            FileUpload::make('video_thumbnail')
                ->disk('public')
                ->visibility('public')
                ->required()
                ->directory('product_video/' . date('FY'))
                ->image()
                ->maxSize(300)
                ->enableDownload(),

            /* ================= VIDEO TYPE ================= */

            Select::make('video_type')
                ->label('Video Type')
                ->options([
                    'youtube_video' => 'Youtube Video',
                    'other_video' => 'Other Video',
                ]) 
                ->required()
                ->live(),

            /* ================= VIDEO FILE ================= */

            FileUpload::make('video_file')
                ->disk('public')
                ->visibility('public')
                ->directory('product_video/' . date('FY'))
                ->acceptedFileTypes(['video/mp4'])
                ->maxSize(10240) // 10MB
                ->visible(fn ($get) => $get('video_type') === 'other_video')
                ->required(fn ($get) => $get('video_type') === 'other_video' && blank($get('video_url')))
                ->live()
                ->rules([
                    fn ($get): \Closure => function ($attribute, $value, $fail) use ($get) {
                        if ($get('video_type') === 'other_video' && filled($value) && filled($get('video_url'))) {
                            $fail('Please upload either Video File OR Video URL, not both.');
                        }
                    },
                ])
                ->validationMessages([
                    'required' => 'Either Video URL or Video File is required.',
                    'max' => 'Video must not exceed 10MB.',
                ])
                ->enableDownload(),

            /* ================= VIDEO URL ================= */

            TextInput::make('video_url')
                ->label('Video URL')
                ->url()
                ->visible(fn ($get) => filled($get('video_type')))
                ->required(fn ($get) =>
                    $get('video_type') === 'youtube_video' ||
                    ($get('video_type') === 'other_video' && blank($get('video_file')))
                )
                ->live()
                // ->rules([
                //     fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {

                //         // ❌ Prevent both
                //         if ($get('video_type') === 'other_video' && filled($value) && filled($get('video_file'))) {
                //             $fail('Please provide either Video URL OR upload Video File, not both.');
                //         }

                //         // ✅ YouTube validation
                //         if ($get('video_type') === 'youtube_video' && ! str_contains($value, 'www.youtube.com')) {
                //             $fail('The URL must be a YouTube link (www.youtube.com).');
                //         }
                //     },
                // ])
                ->rules([
                    fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {

                        // ❌ Prevent both (already working)
                        if ($get('video_type') === 'other_video' && filled($value) && filled($get('video_file'))) {
                            $fail('Please provide either Video URL OR upload Video File, not both.');
                        }

                        // ✅ YouTube validation (supports shorts, watch, youtu.be)
                        if ($get('video_type') === 'youtube_video') {
                            if (! preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\//', $value)) {
                                $fail('Please enter a valid YouTube video URL.');
                            }
                        }

                        // ✅ Direct video URL validation (for other_video)
                        if ($get('video_type') === 'other_video' && filled($value)) {

                            $allowedExtensions = ['mp4', 'webm', 'ogg'];

                            $extension = strtolower(pathinfo(parse_url($value, PHP_URL_PATH), PATHINFO_EXTENSION));

                            if (! in_array($extension, $allowedExtensions)) {
                                $fail('Only valid video URLs are allowed (mp4, webm, ogg).');
                            }
                        }
                    },
                ])
                ->validationMessages([
                    'required' => 'Either Video URL or Video File is required.',
                    'url' => 'Please enter a valid URL.',
                ]),

            /* ================= ORDER ================= */

            TextInput::make('orderby')
                ->label('Order')
                ->numeric()
                ->minValue(1)
                ->default(null)
                ->validationMessages([
                    'min_value' => 'Order must be greater than 0.',
                ]),

            /* ================= STATUS ================= */

            Toggle::make('status')
                ->label('Status')
                ->default(true),

            /* ================= HIDDEN ================= */

            Hidden::make('created_by')
                ->default(fn ($record) => $record?->created_by ?? auth()->id()),
        ]);
    }

}
