<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Technology;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the talent record
        $candidate = static::getModel()::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'linkedin' => $data['linkedin_url'] ?? null,
            'url' => $data['url'] ?? null,
            'job_role_id' => $data['job_role_id'],
        ]);

        // Log the created candidate
        Log::info('Created candidate:', $candidate->toArray());

        // Check if both technologies and technology_details exist
        if (
            isset($data['technologies']) && is_array($data['technologies']) &&
            isset($data['technology_details']) && is_array($data['technology_details'])
        ) {

            // Create a map of technology IDs to years of experience
            $technologyMap = [];
            foreach ($data['technology_details'] as $technologyDetail) {
                $technologyMap[$technologyDetail['technology_name']] = $technologyDetail['years_experience'];
            }

            // Attach technologies with years of experience
            foreach ($data['technologies'] as $technologyId) {
                $technologyName = $this->getTechnologyNameById($technologyId);
                $yearsExperience = $technologyMap[$technologyName] ?? null;

                $candidate->technologies()->attach($technologyId, [
                    'years_experience' => $yearsExperience,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Attached technology ID {$technologyId} to candidate ID {$candidate->id} with {$yearsExperience} years of experience");
            }
        } else {
            Log::warning('No valid technologies data found to attach to the candidate.');
        }

        return $candidate;
    }

    private function getTechnologyNameById($technologyId)
    {
        // Implement this method to get the technology name by ID
        // You might want to cache this to avoid multiple database queries
        return Technology::find($technologyId)->name;
    }
}
