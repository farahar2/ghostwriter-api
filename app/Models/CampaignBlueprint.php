<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignBlueprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'tone_description',
        'max_characters',
        'max_hashtags',
        'extra_rules',
    ];

    protected $casts = [
        'extra_rules' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rawContents(): HasMany
    {
        return $this->hasMany(RawContent::class);
    }
}