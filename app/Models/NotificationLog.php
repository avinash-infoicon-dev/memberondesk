<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'business_id',
        'member_id',
        'channel',
        'type',
        'to',
        'message',
        'status',
        'sent_at',
        'error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'type' => NotificationType::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
