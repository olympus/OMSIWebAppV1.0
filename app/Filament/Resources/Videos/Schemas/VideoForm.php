<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->regex('/^[a-zA-Z0-9\s]+$/')   
                    ->rule('max:255') // strict validation
                    ->validationMessages([
                        'required' => 'Title is required.',
                        'regex' => 'Title should not contain special characters.',
                        'max' => 'Title cannot exceed 255 characters.',
                    ])
                    ->live(onBlur: true), 


                FileUpload::make('videos_thumbnail_image')
                    ->label('Thumbnail Image')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('video/' . date('FY'))
                    ->enableDownload()
                    ->image()
                    ->required()
                    ->maxSize(200)
                    ->validationMessages([
                        'required' => 'Thumbnail image is required.',
                        'max' => 'Image size must not exceed 1MB.',
                    ]),

                Select::make('video_type')
                    ->label('Video Type')
                    ->options([
                        'youtube_video' => 'Youtube Video',
                        'other_video' => 'Other Video',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set) {

                        $set('url', null);
                        $set('video_file', null);

                    }),

                TextInput::make('url')
                    ->label('Video URL')
                    ->visible(fn ($get) => $get('video_type') !== null)
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (filled($state)) {
                            $set('video_file', null);
                        }
                    })
                    // ✅ Required logic
                    ->required(fn ($get) =>
                        $get('video_type') === 'youtube_video'
                        || ($get('video_type') === 'other_video' && blank($get('video_file')))
                    )
                    ->validationAttribute('video URL')
                    ->rules(function (Get $get): array {
                        return [
                            function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if ($get('video_type') === 'youtube_video') {
                                    if (! filled($value)) {
                                        return;
                                    }
                                    if (! preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/', (string) $value)) {
                                        $fail('Enter valid YouTube URL.');
                                    }
                                }
                                if ($get('video_type') === 'other_video') {
                                    $trimmed = trim((string) $value);
                                    if ($trimmed === '') {
                                        return;
                                    }

                                    if (! filter_var($trimmed, FILTER_VALIDATE_URL)) {
                                        $fail('Enter a valid video URL.');
                                    } else {
                                        $parsed = parse_url($trimmed);
                                        $scheme = strtolower($parsed['scheme'] ?? '');
                                        if (! in_array($scheme, ['http', 'https'], true)) {
                                            $fail('Video URL must use http or https.');
                                        } else {
                                            $path = $parsed['path'] ?? '';
                                            if ($path === '' || $path === '/') {
                                                $fail('Enter a direct link to the video file (include the full path to the file, not only the domain).');
                                            } else {
                                                $extension = strtolower(pathinfo(basename($path), PATHINFO_EXTENSION));
                                                $videoExtensions = [
                                                    'mp4', 'webm', 'ogg', 'ogv', 'mov', 'm4v', 'avi', 'mkv',
                                                    'wmv', 'flv', '3gp', 'm3u8', 'ts',
                                                ];
                                                if ($extension === '' || ! in_array($extension, $videoExtensions, true)) {
                                                    $fail('Video URL must point to a file with a video extension (e.g. .mp4, .webm, .mov).');
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                        ];
                    })
                    ->validationMessages([
                        'required' => 'Video URL is required.',
                    ]),

                FileUpload::make('video_file')
                    ->label('Video File')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('video/' . date('FY'))
                    ->enableDownload()
                    ->acceptedFileTypes([
                        'video/mp4'
                    ])
                    ->afterStateUpdated(function ($state, $set) {
                        if (filled($state)) {
                            $set('url', null);
                        }
                    })
                    ->visible(fn ($get) => $get('video_type') === 'other_video')
                    // ✅ Required only when URL empty
                    ->required(fn ($get) =>
                        $get('video_type') === 'other_video'
                        && blank($get('url'))
                    )
                    ->maxSize(10240)
                    ->validationMessages([
                        'required' => 'Video File is required when URL is empty.',
                        'max' => 'Video size must not exceed 10MB.',
                    ]),
 
                Textarea::make('description') 
                    ->columnSpanFull(),

                Toggle::make('enabled')
                    ->label('Status')
                    ->default(true)
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

                Toggle::make('is_trending')
                    ->label('Is Trending')
                    ->default(false)
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

                TextInput::make('order_by')
                    ->label('Order')
                    ->numeric()
                    ->minValue(1)
                    ->default(null)
                    ->validationMessages([
                        'min_value' => 'Order must be greater than 0.',
                    ]),
            ]);
    }
}
