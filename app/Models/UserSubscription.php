<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'last_order_id',
        'status',
        'auto_renew',
        'started_at',
        'expires_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'auto_renew' => 'boolean',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function lastOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'last_order_id');
    }
}
