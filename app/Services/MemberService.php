<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function __construct(
        private readonly QrCodeService $qrCodes,
        private readonly AuditService $audit,
    ) {}

    public function create(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $data['member_code'] ??= $this->nextMemberCode();
            $data['status'] ??= MemberStatus::Active->value;
            $data['joined_at'] ??= now()->toDateString();

            $member = Member::query()->create($data);
            $this->qrCodes->generateFor($member);
            $this->audit->log('member.created', $member, new: $member->toArray());

            return $member->fresh(['activeQrCode']);
        });
    }

    public function update(Member $member, array $data): Member
    {
        $old = $member->toArray();
        $member->update($data);
        $this->audit->log('member.updated', $member, $old, $member->fresh()->toArray());

        return $member->fresh();
    }

    public function delete(Member $member): void
    {
        $old = $member->toArray();
        $member->delete();
        $this->audit->log('member.deleted', $member, $old);
    }

    public function nextMemberCode(): string
    {
        $prefix = 'M'.now()->format('y');
        $latest = Member::query()
            ->withTrashed()
            ->where('member_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('member_code');

        $sequence = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
