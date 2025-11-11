<?php

namespace App\Filament\Resources\Promailers\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str; 

class PromailerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')
                ->label('Title')
                ->required(),
            TextInput::make('nt_title')
                ->label('Notification Title')
                ->required(), 
            TextInput::make('nt_body')
                ->label('Notification Body')
                ->required(),
            Textarea::make('abbreviation')
                ->label('Abbreviation')
                ->required(),
            Toggle::make('status')
                ->label('Status')
                ->default(1), 
            FileUpload::make('frontimage')
                ->label('Featured Image')
                ->directory('promailers')  
                ->disk('public') 
                ->image()
                ->columnSpanFull()
                ->nullable(),

            Builder::make('body')
                ->label('Mail Content Builder')
                ->blocks([
                    Block::make('title')
                        ->label('Title')
                        ->schema([
                            Textarea::make('value')
                                ->label('Title') 
                                ->required(), 
                        ])
                        ->icon('heroicon-o-document-text'),

                    Block::make('paragraph')
                        ->label('Paragraph')
                        ->schema([
                            Textarea::make('value')
                                ->label('Paragraph Text')
                                ->rows(5)
                                ->required(), 
                        ])
                        ->icon('heroicon-o-document-text'),

                    Block::make('url')
                        ->label('URL')
                        ->schema([
                            TextInput::make('value')
                                ->label('Link URL')
                                ->url()
                                ->required(), 
                        ])
                        ->icon('heroicon-o-link'),

                     

                    Block::make('image')
                        ->label('Image')
                        ->schema([
                            FileUpload::make('value')
                                ->label('Upload Image')
                                ->image()
                                ->disk('public')
                                ->directory('promailers/images')
                                ->preserveFilenames()
                                ->required(),

                            TextInput::make('file_name')
                                ->default(fn ($get) => basename($get('value')))
                                ->hidden(),
                        ])
                        ->icon('heroicon-o-photo'),

                    Block::make('pdf')
                        ->label('PDF')
                        ->schema([
                            FileUpload::make('value')
                                ->label('Upload PDF')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('promailers/pdfs')
                                ->disk('public')
                                ->preserveFilenames()
                                ->required(),

                            TextInput::make('file_name')
                                ->default(fn ($get) => basename($get('value')))
                                ->hidden(),
                        ])
                        ->icon('heroicon-o-document-arrow-down'),
                ])
                ->collapsible()
                ->cloneable()
                ->reorderable()
                ->createItemButtonLabel('Add Section'),
        ]);
    }
}
