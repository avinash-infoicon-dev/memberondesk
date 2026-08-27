<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'business_id',
        'member_id',
        'membership_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'amount',
        'paid_amount',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isCurrentlyActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && $this->starts_at->lte(now())
            && $this->ends_at->gte(now()->startOfDay());
    }

    public function isPaidInFull(): bool
    {
        return (float) $this->paid_amount >= (float) $this->amount;
    }

    public function balance(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active)
            ->whereDate('starts_at', '<=', now())
            ->whereDate('ends_at', '>=', now());
    }

    public function scopeExpiring(Builder $query, int $days = 7): Builder
    {
        return $query->where('status', SubscriptionStatus::Active)
            ->whereDate('ends_at', '>=', now())
            ->whereDate('ends_at', '<=', now()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->where('status', SubscriptionStatus::Expired)
                ->orWhere(function (Builder $inner) {
                    $inner->where('status', SubscriptionStatus::Active)
                        ->whereDate('ends_at', '<', now());
                });
        });
    }
}
