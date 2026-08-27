<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentRequestStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'business_id',
        'member_id',
        'subscription_id',
        'amount',
        'status',
        'token',
        'method',
        'upi_id',
        'gateway',
        'gateway_order_id',
        'expires_at',
        'paid_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentRequestStatus::class,
            'method' => PaymentMethod::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isOpen(): bool
    {
        return $this->status === PaymentRequestStatus::Pending
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function upiLink(): string
    {
        $params = http_build_query([
            'pa' => $this->upi_id,
            'pn' => $this->business?->name ?? 'Member On Desk',
            'am' => number_format((float) $this->amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => 'Membership payment '.$this->token,
        ]);

        return 'upi://pay?'.$params;
    }
}
