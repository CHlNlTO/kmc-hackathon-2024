<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use App\Models\Technology;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Str;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Personal Details')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->maxLength(255),
                                TextInput::make('linkedin')
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('url')
                                    ->url()
                                    ->maxLength(255),
                                Select::make('job_role_id')
                                    ->relationship('jobRole', 'name')
                                    ->required(),
                            ])
                        ]),
                    Step::make('Technologies/Tools')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('technologies')
                                    ->multiple()
                                    ->options(function () {
                                        return Technology::all()->pluck('name', 'id');
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $currentTechnologyDetails = $get('technology_details') ?? [];

                                        // Create a map of existing technology details
                                        $existingTechnologyMap = collect($currentTechnologyDetails)->keyBy('technology')->toArray();

                                        // Create or update technology details
                                        $updatedTechnologyDetails = collect($state)->map(function ($technologyId) use ($existingTechnologyMap) {
                                            $technologyName = Technology::find($technologyId)->name;
                                            return [
                                                'technology' => $technologyId,
                                                'technology_name' => $technologyName,
                                                'years_experience' => $existingTechnologyMap[$technologyId]['years_experience'] ?? null,
                                            ];
                                        })->toArray();

                                        $set('technology_details', $updatedTechnologyDetails);
                                    })->columnStart(1)
                            ]),
                            Repeater::make('technology_details')
                                ->schema([
                                    Select::make('technology')
                                        ->options(function () {
                                            return Technology::all()->pluck('name', 'id');
                                        })
                                        ->disabled(),
                                    TextInput::make('years_experience')
                                        ->numeric()
                                        ->label('Years of Experience')
                                        ->required(),
                                ])
                                ->columns(2)
                                ->disableItemCreation()
                                ->disableItemDeletion()
                                ->disableItemMovement()
                                ->columnSpanFull(),
                        ]),
                ])
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->sortable(['first_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Str::limit($state, 10)),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Str::limit($state, 10)),
                Tables\Columns\TextColumn::make('linkedin')
                    ->searchable()
                    ->sortable()
                    ->label('LinkedIn')
                    ->formatStateUsing(fn($state) => Str::limit(str_replace('https://www.linkedin.com/in/', '', $state), 23)),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->sortable()
                    ->label('URL')
                    ->formatStateUsing(fn($state) => Str::limit(str_replace('https://', '', $state), 23)),
                Tables\Columns\TextColumn::make('jobRole.name')
                    ->sortable()
                    ->searchable()
                    ->label('Job Role'),
                Tables\Columns\TextColumn::make('technologies.name')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->tooltip(fn(Candidate $record): string => $record->technologies->pluck('name')->implode(', ')),
            ])
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CandidateResource\RelationManagers\TechnologiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}
