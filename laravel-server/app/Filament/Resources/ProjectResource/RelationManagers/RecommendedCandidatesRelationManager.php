<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Candidate;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecommendedCandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'technologies';
    protected static ?string $title = 'Suggested Candidates';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                function () {
                    // Get project's technology requirements
                    $projectTechnologies = $this->ownerRecord->technologies()
                        ->withPivot('years_experience')
                        ->get()
                        ->pluck('id')
                        ->toArray();

                    $projectId = $this->ownerRecord->id;
                    $technologyIds = implode(',', $projectTechnologies);

                    return Candidate::query()
                        ->with(['technologies', 'jobRole'])
                        ->whereHas('technologies', function (Builder $query) use ($projectTechnologies) {
                            $query->whereIn('technologies.id', $projectTechnologies);
                        })
                        ->select(['candidates.*'])
                        ->selectRaw("
                            (
                                SELECT COUNT(DISTINCT ct.technology_id)
                                FROM candidate_technology ct
                                WHERE ct.candidate_id = candidates.id
                                AND ct.technology_id IN ({$technologyIds})
                            ) as matching_tech_count
                        ")
                        ->selectRaw("
                            (
                                SELECT COALESCE(SUM(
                                    CASE
                                        WHEN ct.years_experience >= pt.years_experience THEN 1
                                        ELSE ct.years_experience / NULLIF(pt.years_experience, 0)
                                    END
                                ), 0)
                                FROM candidate_technology ct
                                JOIN project_technology pt ON ct.technology_id = pt.technology_id
                                WHERE ct.candidate_id = candidates.id
                                AND pt.project_id = {$projectId}
                            ) as experience_score
                        ")
                        ->orderByDesc('matching_tech_count')
                        ->orderByDesc('experience_score');
                }
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('jobRole.name')
                    ->sortable()
                    ->searchable()
                    ->label('Job Role'),
                Tables\Columns\TextColumn::make('technologies.name')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->tooltip(fn(Candidate $record): string => $record->technologies->pluck('name')->implode(', '))
                    ->label('Technologies'),
                Tables\Columns\TextColumn::make('matching_tech_count')
                    ->label('Matching Technologies')
                    ->sortable(),
                Tables\Columns\TextColumn::make('experience_score')
                    ->label('Experience Match Score')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->tooltip(function (Candidate $record) {
                        $projectTechs = $this->ownerRecord->technologies()
                            ->withPivot('years_experience')
                            ->get();

                        $explanation = "Points:\n";
                        $explanation .= "Meet/Exceed years needed: 1.00\n";
                        $explanation .= "Below needed: percentage match\n\n";

                        foreach ($projectTechs as $tech) {
                            $candidateExp = $record->technologies()
                                ->wherePivot('technology_id', $tech->id)
                                ->first()?->pivot?->years_experience ?? 0;

                            $reqExp = $tech->pivot->years_experience;
                            $score = $candidateExp >= $reqExp ? 1 : ($candidateExp / $reqExp);

                            $explanation .= sprintf(
                                "%s: %d/%d yr (%.2f)\n",
                                $tech->name,
                                $candidateExp,
                                $reqExp,
                                $score
                            );
                        }

                        return $explanation;
                    }),
            ])
            ->defaultSort('matching_tech_count', 'desc')
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Model $record): string => route('filament.admin.resources.candidates.edit', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
