<?php

namespace App\Models;

use App\Enums\MembershipDisplayStatus;
use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'business_id',
        'member_code',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'photo_path',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'joined_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'date_of_birth' => 'date',
            'joined_at' => 'date',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->ofMany(['id' => 'max'], function ($query) {
            $query->where('status', SubscriptionStatus::Active)
                ->whereDate('starts_at', '<=', now())
                ->whereDate('ends_at', '>=', now());
        });
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(MemberQrCode::class);
    }

    public function activeQrCode(): HasOne
    {
        return $this->hasOne(MemberQrCode::class)->ofMany(['id' => 'max'], function ($query) {
            $query->where('is_active', true);
        });
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function displayStatus(): MembershipDisplayStatus
    {
        if ($this->status === MemberStatus::Inactive) {
            return MembershipDisplayStatus::Inactive;
        }

        $active = $this->relationLoaded('activeSubscription')
            ? $this->activeSubscription
            : $this->activeSubscription()->first();

        if ($active) {
            if ($active->balance() > 0) {
                return MembershipDisplayStatus::PaymentPending;
            }

            if ($active->ends_at?->lte(now()->addDays(7)->endOfDay())) {
                return MembershipDisplayStatus::ExpiringSoon;
            }

            return MembershipDisplayStatus::Active;
        }

        $pendingUnpaid = $this->subscriptions()
            ->where('status', SubscriptionStatus::Pending)
            ->whereColumn('paid_amount', '<', 'amount')
            ->exists();

        if ($pendingUnpaid) {
            return MembershipDisplayStatus::PaymentPending;
        }

        if ($this->subscriptions()->expired()->exists()) {
            return MembershipDisplayStatus::Expired;
        }

        return MembershipDisplayStatus::Inactive;
    }

    public function scopeDisplayStatus(Builder $query, MembershipDisplayStatus|string $status): Builder
    {
        $status = $status instanceof MembershipDisplayStatus
            ? $status
            : MembershipDisplayStatus::from($status);

        return match ($status) {
            MembershipDisplayStatus::Inactive => $query->where(function (Builder $builder) {
                $builder->where('status', MemberStatus::Inactive)
                    ->orWhere(function (Builder $inner) {
                        $inner->where('status', MemberStatus::Active)
                            ->whereDoesntHave('subscriptions', function (Builder $subscriptions) {
                                $subscriptions->where(function (Builder $row) {
                                    $row->active()
                                        ->orWhere('status', SubscriptionStatus::Pending)
                                        ->orWhere(fn (Builder $expired) => $expired->expired());
                                });
                            });
                    });
            }),
            MembershipDisplayStatus::PaymentPending => $query->where('status', MemberStatus::Active)
                ->whereHas('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->whereColumn('paid_amount', '<', 'amount')
                        ->where(function (Builder $row) {
                            $row->active()->orWhere('status', SubscriptionStatus::Pending);
                        });
                }),
            MembershipDisplayStatus::ExpiringSoon => $query->where('status', MemberStatus::Active)
                ->whereHas('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->expiring(7)->whereColumn('paid_amount', '>=', 'amount');
                })
                ->whereDoesntHave('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->whereColumn('paid_amount', '<', 'amount')
                        ->where(function (Builder $row) {
                            $row->active()->orWhere('status', SubscriptionStatus::Pending);
                        });
                }),
            MembershipDisplayStatus::Active => $query->where('status', MemberStatus::Active)
                ->whereHas('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->active()
                        ->whereDate('ends_at', '>', now()->addDays(7))
                        ->whereColumn('paid_amount', '>=', 'amount');
                }),
            MembershipDisplayStatus::Expired => $query->where('status', MemberStatus::Active)
                ->whereDoesntHave('subscriptions', fn (Builder $subscriptions) => $subscriptions->active())
                ->whereDoesntHave('subscriptions', function (Builder $subscriptions) {
                    $subscriptions->where('status', SubscriptionStatus::Pending)
                        ->whereColumn('paid_amount', '<', 'amount');
                })
                ->whereHas('subscriptions', fn (Builder $subscriptions) => $subscriptions->expired()),
        };
    }
}
