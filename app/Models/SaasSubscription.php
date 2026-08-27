<?php

namespace App\Models;

use App\Enums\SaasSubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasSubscription extends Model
{
    protected $fillable = [
        'business_id',
        'saas_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SaasSubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function isLive(): bool
    {
        return in_array($this->status, [
            SaasSubscriptionStatus::Trial,
            SaasSubscriptionStatus::Active,
        ], true) && $this->ends_at->isFuture();
    }
}
