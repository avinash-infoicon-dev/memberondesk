<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\SaasSubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'owner_id',
        'email',
        'phone',
        'upi_id',
        'address',
        'city',
        'state',
        'pincode',
        'logo_path',
        'status',
        'timezone',
        'whatsapp_enabled',
        'settings',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BusinessType::class,
            'status' => BusinessStatus::class,
            'whatsapp_enabled' => 'boolean',
            'settings' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $business): void {
            if (empty($business->slug)) {
                $business->slug = Str::slug($business->name).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function saasSubscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    public function currentSaasSubscription(): HasOne
    {
        return $this->hasOne(SaasSubscription::class)->latestOfMany();
    }

    public function saasPayments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === BusinessStatus::Active;
    }

    public function hasActiveSaas(): bool
    {
        $subscription = $this->currentSaasSubscription;

        if (! $subscription) {
            return false;
        }

        return in_array($subscription->status, [
            SaasSubscriptionStatus::Trial,
            SaasSubscriptionStatus::Active,
        ], true) && $subscription->ends_at?->isFuture();
    }
}
