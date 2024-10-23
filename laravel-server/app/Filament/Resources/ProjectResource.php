<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Project Title')
                    ->columnSpan('full'),
                RichEditor::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Project Description'),
                TextInput::make('budget')
                    ->numeric()
                    ->prefix('₱')
                    ->maxValue(42949672.95)
                    ->label('Project Budget'),
                TextInput::make('location')
                    ->maxLength(255)
                    ->label('Project Location'),
                DatePicker::make('timeline')
                    ->label('Project Deadline'),
                Select::make('technologies')
                    ->relationship('technologies', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Required Technologies'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('timeline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('technologies.name')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->tooltip(fn(Project $record): string => $record->technologies->pluck('name')->implode(', ')),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
