<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Str;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;

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
                Section::make('Personal Information')
                    ->schema([
                        TextEntry::make('full_name')->weight(FontWeight::Bold),
                        TextEntry::make('email')
                            ->url(fn($state) => "mailto:$state")
                            ->weight(FontWeight::Bold),
                        TextEntry::make('phone')->weight(FontWeight::Bold),
                        TextEntry::make('address')->weight(FontWeight::Bold),
                        TextEntry::make('linkedin')
                            ->label('LinkedIn')
                            ->weight(FontWeight::Bold)
                            ->formatStateUsing(
                                fn($state) =>
                                Str::replace('https://www.linkedin.com/in/', '', $state)
                            ),
                        TextEntry::make('url')
                            ->label('Portfolio')
                            ->weight(FontWeight::Bold)
                            ->formatStateUsing(
                                fn($state) =>
                                Str::replace('https://', '', $state)
                            ),
                        TextEntry::make('jobRole.name')
                            ->label('Job Role')
                            ->weight(FontWeight::Bold),
                    ])
                    ->compact()
                    ->columns(2),
            ]);
    }
}
