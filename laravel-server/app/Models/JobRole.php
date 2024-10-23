<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobRole extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
