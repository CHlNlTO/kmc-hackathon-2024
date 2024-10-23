<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Str;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('title')->weight(FontWeight::Bold),
                        TextEntry::make('budget')
                            ->money('PHP')->weight(FontWeight::Bold),
                        TextEntry::make('location')->weight(FontWeight::Bold),
                        TextEntry::make('timeline')
                            ->date()->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->html()
                            ->columnSpanFull()->weight(FontWeight::Bold)
                            ->formatStateUsing(fn($state) => Str::limit($state, 100)),
                        TextEntry::make('technologies.name')
                            ->separator(', ')->weight(FontWeight::Bold),
                    ])
                    ->compact()
                    ->columns(2),
                // Section::make('Required Technology/Tools')
                //     ->schema([
                //         RepeatableEntry::make('technologies')
                //             ->label(' ')
                //             ->schema([
                //                 TextEntry::make('name')
                //                     ->label(' ')
                //                     ->weight(FontWeight::Bold),
                //                 TextEntry::make('pivot.years_experience')
                //                     ->label(' ')
                //                     ->weight(FontWeight::Bold)
                //             ])
                //             ->columns(2)
                //             ->contained(false)
                //     ])
                //     ->compact(),
            ]);
    }
}
