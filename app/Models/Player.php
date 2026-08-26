<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id', 'api_football_id', 'name', 'position', 'shirt_number', 'nationality',
        'birth_date', 'birth_place', 'birth_country', 'height', 'weight', 'injured', 'photo_url',
        'is_captain', 'goals', 'assists', 'stats', 'trophies', 'transfers',
        'meta_description',
    ];

    protected $casts = [
        'is_captain' => 'boolean',
        'injured' => 'boolean',
        'birth_date' => 'date',
        'stats' => 'array',
        'trophies' => 'array',
        'transfers' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function seoSlug(): string
    {
        return Str::slug($this->name);
    }

    public function prettyUrl(): string
    {
        return route('players.show', ['player' => $this->id, 'slug' => $this->seoSlug()]);
    }
}
