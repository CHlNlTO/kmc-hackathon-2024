<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'budget',
        'location',
        'timeline'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'timeline' => 'date'
    ];

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'project_technology')
            ->withPivot('years_experience')
            ->withTimestamps();
    }
}
