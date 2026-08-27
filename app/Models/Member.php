<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Concerns\BelongsToTenant;
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
}
