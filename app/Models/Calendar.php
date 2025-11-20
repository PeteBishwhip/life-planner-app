<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'color',
        'is_visible',
        'is_default',
        'description',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Validation rules for calendar creation/update
     */
    public static function rules(bool $isUpdate = false): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:personal,business,custom'],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_visible' => ['boolean'],
            'is_default' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Boot method to handle model events
     */
    protected static function booted(): void
    {
        static::creating(function (Calendar $calendar) {
            // Set default color if not provided
            if (empty($calendar->color)) {
                $calendar->color = match($calendar->type) {
                    'personal' => '#3B82F6', // Blue
                    'business' => '#10B981', // Green
                    'custom' => '#8B5CF6',   // Purple
                    default => '#6B7280',     // Gray
                };
            }
        });

        // Ensure only one default calendar per user
        static::saving(function (Calendar $calendar) {
            if ($calendar->is_default) {
                self::where('user_id', $calendar->user_id)
                    ->where('id', '!=', $calendar->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Query Scopes
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
