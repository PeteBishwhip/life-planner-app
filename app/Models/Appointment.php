<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
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
}
