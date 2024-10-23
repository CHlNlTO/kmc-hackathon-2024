<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->syncTechnologies($data);
        unset($data['technologies']); // Remove to avoid inserting twice

        return $data;
    }

    private function syncTechnologies(array &$data)
    {
        $technologies = $data['technologies'] ?? [];

        $this->record->syncTechnologies($technologies);
    }
}
