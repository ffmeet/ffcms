<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'cover_image_url',
        'location',
        'is_paid',
        'price',
        'required_member_group_id',
        'starts_at',
        'ends_at',
        'registration_opens_at',
        'registration_closes_at',
        'capacity',
        'summary',
        'content',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function memberGroup(): BelongsTo
    {
        return $this->belongsTo(MemberGroup::class, 'required_member_group_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function hasReachedCapacity(): bool
    {
        if (! $this->capacity) {
            return false;
        }

        if ($this->relationLoaded('registrations')) {
            return $this->registrations
                ->whereIn('status', ['pending', 'approved'])
                ->count() >= $this->capacity;
        }

        return $this->registrations()
            ->whereIn('status', ['pending', 'approved'])
            ->count() >= $this->capacity;
    }

    public function isRegistrationAvailable(): bool
    {
        if ($this->status !== 'registration-open') {
            return false;
        }

        if ($this->registration_closes_at?->isPast()) {
            return false;
        }

        return ! $this->hasReachedCapacity();
    }
}
