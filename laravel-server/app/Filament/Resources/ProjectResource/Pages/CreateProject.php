<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    #[On('fill-project-form')]
    public function handleProjectDetails($details)
    {
        try {
            // Log the received details for debugging
            Log::info('Received project details:', $details);

            // Format the timeline if it exists
            $timeline = null;
            if (!empty($details['timeline'])) {
                try {
                    // Ensure timeline is in Y-m-d format
                    $timeline = Carbon::parse($details['timeline'])->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::error('Error formatting timeline: ' . $e->getMessage());
                }
            }

            // Fill the form with the extracted data
            $this->form->fill([
                'title' => $details['title'] ?? null,
                'description' => $details['description'] ?? null,
                'budget' => $details['budget'] ? number_format($details['budget'], 2, '.', '') : null,
                'location' => $details['location'] ?? null,
                'timeline' => $timeline,
                'technologies' => $details['technology_ids'] ?? [],
            ]);

            // Show a success notification
            Notification::make()
                ->title('Form filled with project details')
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error filling form:', ['error' => $e->getMessage()]);

            // Show error notification
            Notification::make()
                ->title('Error filling form')
                ->body('There was an error filling the form: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['budget'])) {
            $data['budget'] = floatval(str_replace(',', '', $data['budget']));
        }

        return $data;
    }
}
