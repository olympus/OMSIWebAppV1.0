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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;

class ProductInformationRelationManager extends RelationManager
{
    protected static string $relationship = 'productInformations';

    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Title')->searchable(),
                TextColumn::make('file_upload')
                ->label('File')
                ->url(fn ($record) => asset('storage/'.$record->file_upload))
                ->openUrlInNewTab(),
 
                ToggleColumn::make('status')->label('Status')->sortable(),
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

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')
                ->label('Title')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) return;
                    $trimmed = trim(preg_replace('/\s+/', ' ', $state));
                    $set('title', $trimmed);
                })
                ->required()
                ->maxLength(100)
                ->helperText('Maximum 100 characters are allowed.')
                ->regex('/^[a-zA-Z0-9\.\-]+( [a-zA-Z0-9\.\-]+)*$/'),
                //->regex('/^[a-zA-Z0-9\-]+( [a-zA-Z0-9\-]+)*$/'),

            Textarea::make('description')
                ->label('Description')
                 ->maxLength(100)
                ->helperText('Maximum 100 characters are allowed.')
                ->rows(4),

            /* ================= FILE UPLOAD ================= */

            FileUpload::make('file_upload')
                    ->label('PDF File Upload')
                    ->disk('public')
                    ->directory('product_pdf/' . date('FY'))
                    ->acceptedFileTypes(['application/pdf'])   // only PDF
                    ->enableOpen()
                    ->enableDownload()
                    ->maxSize(2048)
                    ->nullable()
                    // remove image → null save
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('file_upload'))
                        && blank($get('file_url'))
                    )
                    ->validationMessages([
                        'required' => 'Either File Upload or File URL is required.',
                        'acceptedFileTypes' => 'Only PDF files are allowed.',
                    ]),
        
            // FileUpload::make('file_upload')
            //     ->disk('public')
            //     ->visibility('public')
            //     ->directory('product_pdf/' . date('FY'))
            //     ->acceptedFileTypes(['application/pdf'])
            //     ->maxSize(5120) // 5MB
            //     ->enableDownload()
            //     ->reactive()
            //     ->afterStateUpdated(function ($state, callable $set) {
            //         if ($state) {
            //             $set('file_url', null); // 🔥 Clear URL if file uploaded
            //         }
            //     })
            //     ->visible(fn ($get) => blank($get('file_url')))
            //     ->required(fn ($get) => blank($get('file_url')))
            //     ->validationMessages([
            //         'required' => 'Either File Upload or File URL is required.',
            //     ]),

            /* ================= FILE URL ================= */
            TextInput::make('file_url')
                    ->label('File URL')
                    ->url()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('file_upload'))
                        && blank($get('file_url'))
                    )
                    // Prevent both filled
                    ->rule(function (Get $get) {
                        return function (
                            $attribute,
                            $value,
                            $fail
                        ) use ($get) {
                            if (
                                filled($get('file_upload'))
                                && filled($value)
                            ) {
                                $fail(
                                    'Please use either File Upload OR File URL.'
                                );
                            }
                        };
                    })
                    ->validationMessages([
                        'required' => 'Either Image Upload or Image URL is required.',
                        'url' => 'Enter valid URL',
                    ]),

            // TextInput::make('file_url')
            //     ->label('File URL')
            //     ->url()
            //     ->reactive()
            //     ->afterStateUpdated(function ($state, callable $set) {
            //         if ($state) {
            //             $set('file_upload', null); // 🔥 Clear file if URL entered
            //         }
            //     })
            //     ->visible(fn ($get) => blank($get('file_upload')))
            //     ->required(fn ($get) => blank($get('file_upload')))
            //     ->validationMessages([
            //         'required' => 'Either File Upload or File URL is required.',
            //         'url' => 'Please enter a valid URL.',
            //     ]),

            TextInput::make('order')
                ->label('Order')
                ->numeric()
                ->minValue(1)
                ->default(null)
                ->validationMessages([
                    'min_value' => 'Order must be greater than 0.',
                ]),   
                
            Toggle::make('status')
                ->default(true),
                
            Hidden::make('created_by')
                ->default(fn ($record) => $record?->created_by ?? auth()->id()),
        ]);
    }

    /*public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->label('Title'),

                Textarea::make('description')
                    ->label('Description')
                    ->nullable(),

                FileUpload::make('file_upload')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('product_pdf/' . date('FY'))
                    ->enableDownload()
                    ->required(), 

                TextInput::make('order')  
                    ->label('Order By'),
                
                Toggle::make('status')
                    ->label('Status')
                    ->default(true),
            ]);
    }*/
}