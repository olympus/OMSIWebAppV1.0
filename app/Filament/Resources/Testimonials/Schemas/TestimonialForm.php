<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Product;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'product_name')
                    ->searchable()
                    ->preload(), 

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->regex('/^[a-zA-Z0-9\s]+$/') 
                    ->rule('max:50') // strict validation
                    ->validationMessages([
                        'required' => 'Name is required.',
                        'regex' => 'Name should not contain special characters.',
                        'max' => 'Name cannot exceed 50 characters.',
                    ])
                    ->live(onBlur: true),

                TextInput::make('designation')
                    ->label('Designation')
                    ->required()
                    ->regex('/^[a-zA-Z0-9\s,\-]+$/') 
                    ->rule('max:50') 
                    ->validationMessages([
                        'required' => 'Designation is required.',
                        'regex' => 'Designation may only contain letters, numbers, spaces, comma and hyphen.',
                        'max' => 'Designation cannot exceed 50 characters.',
                    ]) 
                    ->live(onBlur: true),

                Select::make('type')
                    ->options([
                        'text' => 'Text',
                        'video' => 'Video',
                    ])
                    ->default('text')
                    ->required()
                    ->label('Testimonial Type (Text or Video)')
                    ->reactive(),

                FileUpload::make('thumbnail_image')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('testimonial/' . date('FY'))
                    ->enableDownload()
                    ->image()
                    ->required() // ✅ REQUIRED
                    ->maxSize(200)
                    ->validationMessages([
                        'required' => 'Thumbnail image is required.',
                        'max' => 'Image size must not exceed 1MB.',
                    ]),

                Textarea::make('message')
                    ->columnSpanFull()
                    ->rule('max:1000')
                    ->visible(fn ($get) => $get('type') === 'text')
                    ->required(fn ($get) => $get('type') === 'text'),


                Select::make('video_type')
                    ->label('Video Type')
                    ->options([
                        'youtube_video' => 'Youtube Video',
                        'other_video' => 'Other Video',
                    ])
                    //->required()
                    ->live()
                    ->visible(fn ($get) => $get('type') === 'video')
                    ->required(fn ($get) => $get('type') === 'video')
                    ->afterStateUpdated(function ($set) {

                        $set('video_url', null);
                        $set('video_file', null);

                    }),


                TextInput::make('video_url')
                    ->label('Video URL')
                    ->visible(fn ($get) => $get('video_type') !== null)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set) {
                        if (filled($state)) {
                            $set('video_file', null);
                        }
                    })
                    ->required(fn ($get) =>
                        $get('video_type') === 'youtube_video'
                        || ($get('video_type') === 'other_video' && blank($get('video_file')))
                    )
                    // ✅ Validation
                    ->rule(function ($get) {
                        return function ($attribute, $value, $fail) use ($get) {
                            if ($get('video_type') === 'youtube_video') {
                                if (!preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/', $value)) {
                                    $fail('Enter valid YouTube URL.');
                                }
                            }
                            if ($get('video_type') === 'other_video') {
                                $trimmed = trim((string) $value);
                                if (blank($trimmed)) {
                                    return;
                                }

                                // URL wins over a stored file path: afterStateUpdated clears video_file when URL is set.
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
                        };
                    })
                    ->validationMessages([
                        'required' => 'Video URL is required.',
                    ]), 

                FileUpload::make('video_file')
                    ->label('Video File')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('testimonial/' . date('FY'))
                    ->enableDownload()
                    ->acceptedFileTypes([
                        'video/mp4'
                    ])
                    ->afterStateUpdated(function ($state, $set) {
                        if (filled($state)) {
                            $set('video_url', null);
                        }
                    })
                    ->visible(fn ($get) => $get('video_type') === 'other_video') 
                    ->required(fn ($get) =>
                        $get('video_type') === 'other_video'
                        && blank($get('video_url'))
                    )
                    ->maxSize(10240)
                    ->validationMessages([
                        'required' => 'Video File is required when URL is empty.',
                        'max' => 'Video size must not exceed 10MB.',
                    ]), 

                Toggle::make('status')
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

                Hidden::make('created_by')
                    ->default(fn ($record) => $record?->created_by ?? auth()->id()),
            ]);
    }
}
