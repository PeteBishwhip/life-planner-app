<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'user_id',
        'title',
        'description',
        'location',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'color',
        'recurrence_rule',
        'recurrence_parent_id',
        'status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_all_day' => 'boolean',
        'recurrence_rule' => 'array',
    ];

    /**
     * Validation rules for appointment creation/update
     */
    public static function rules(bool $isUpdate = false): array
    {
        return [
            'calendar_id' => ['required', 'exists:calendars,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:500'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'is_all_day' => ['boolean'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'recurrence_rule' => ['nullable', 'array'],
            'recurrence_parent_id' => ['nullable', 'exists:appointments,id'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
        ];
    }

    /**
     * Boot method to handle model events
     */
    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            // Inherit color from calendar if not provided
            if (empty($appointment->color) && $appointment->calendar) {
                $appointment->color = $appointment->calendar->color;
            }

            // Set default status
            if (empty($appointment->status)) {
                $appointment->status = 'scheduled';
            }
        });
    }

    /**
     * Relationships
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'recurrence_parent_id');
    }

    public function recurrenceInstances(): HasMany
    {
        return $this->hasMany(Appointment::class, 'recurrence_parent_id');
    }

    /**
     * Query Scopes
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCalendar(Builder $query, int $calendarId): Builder
    {
        return $query->where('calendar_id', $calendarId);
    }

    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_datetime', [$startDate, $endDate])
              ->orWhereBetween('end_datetime', [$startDate, $endDate])
              ->orWhere(function ($q) use ($startDate, $endDate) {
                  $q->where('start_datetime', '<=', $startDate)
                    ->where('end_datetime', '>=', $endDate);
              });
        });
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_datetime', '>=', now())
                    ->where('status', 'scheduled')
                    ->orderBy('start_datetime', 'asc');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeAllDay(Builder $query): Builder
    {
        return $query->where('is_all_day', true);
    }

    /**
     * Helper Methods
     */
    public function isRecurring(): bool
    {
        return !empty($this->recurrence_rule);
    }

    public function hasConflict(int $calendarId, $start, $end, ?int $excludeId = null): bool
    {
        $query = self::forCalendar($calendarId)
            ->where('status', 'scheduled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                  ->orWhereBetween('end_datetime', [$start, $end])
                  ->orWhere(function ($q) use ($start, $end) {
                      $q->where('start_datetime', '<=', $start)
                        ->where('end_datetime', '>=', $end);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getDurationInMinutes(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }
}
