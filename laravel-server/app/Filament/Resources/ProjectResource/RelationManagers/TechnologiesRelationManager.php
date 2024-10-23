<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TechnologiesRelationManager extends RelationManager
{
    protected static string $relationship = 'technologies';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Required Technologies';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('technology_id')
                    ->relationship('technology', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('years_experience')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->label('Years of Experience'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Technology')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('years_experience')
                    ->label('Years of Experience')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidates_count')
                    ->label('Candidates')
                    ->counts('candidates')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
