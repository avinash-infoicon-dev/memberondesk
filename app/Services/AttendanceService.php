<?php

namespace App\Services;

use App\Enums\AttendanceSource;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private readonly QrCodeService $qrCodes,
        private readonly AuditService $audit,
    ) {}

    public function scan(string $token, ?User $scanner = null): Attendance
    {
        $qr = $this->qrCodes->findActiveByToken($token);

        if (! $qr) {
            throw ValidationException::withMessages([
                'token' => 'Invalid or inactive QR code.',
            ]);
        }

        $member = $qr->member()->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'token' => 'Member not found for this QR code.',
            ]);
        }

        return $this->checkIn($member, AttendanceSource::Qr, $scanner);
    }

    public function checkIn(
        Member $member,
        AttendanceSource $source = AttendanceSource::Manual,
        ?User $scanner = null,
    ): Attendance {
        $subscription = $member->subscriptions()
            ->active()
            ->latest('id')
            ->first();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'member' => 'No active membership found. Attendance was not recorded.',
            ]);
        }

        $open = Attendance::query()
            ->where('member_id', $member->id)
            ->whereDate('check_in_at', now())
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if ($open) {
            $open->update(['check_out_at' => now()]);
            $this->audit->log('attendance.checked_out', $open);

            return $open->fresh(['member', 'subscription']);
        }

        return DB::transaction(function () use ($member, $subscription, $source, $scanner) {
            $attendance = Attendance::query()->create([
                'business_id' => $member->business_id,
                'member_id' => $member->id,
                'subscription_id' => $subscription->id,
                'scanned_by' => $scanner?->id,
                'check_in_at' => now(),
                'source' => $source,
            ]);

            $this->audit->log('attendance.checked_in', $attendance);

            return $attendance->fresh(['member', 'subscription']);
        });
    }
}
