<?php

namespace App\Models;

use App\Enums\AttendanceSource;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToTenant;

    protected $table = 'attendance';

    protected $fillable = [
        'business_id',
        'member_id',
        'subscription_id',
        'scanned_by',
        'check_in_at',
        'check_out_at',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source' => AttendanceSource::class,
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
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

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
