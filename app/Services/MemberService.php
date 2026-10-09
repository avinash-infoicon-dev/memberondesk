<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function __construct(
        private readonly QrCodeService $qrCodes,
        private readonly AuditService $audit,
    ) {}

    public function create(array $data): Member
    {
        $data['status'] ??= MemberStatus::Active->value;
        $data['joined_at'] ??= now()->toDateString();
        unset($data['member_code']);

        return DB::transaction(function () use ($data) {
            $attempts = 0;

            while (true) {
                try {
                    $data['member_code'] = $this->nextMemberCode();
                    $member = Member::query()->create($data);
                    $this->qrCodes->generateFor($member);
                    $this->audit->log('member.created', $member, new: $member->toArray());

                    return $member->fresh(['activeQrCode']);
                } catch (QueryException $e) {
                    if (++$attempts >= 8 || ! $this->isMemberCodeDuplicate($e)) {
                        throw $e;
                    }
                }
            }
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

        $maxSequence = Member::query()
            ->withTrashed()
            ->where('member_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('member_code')
            ->reduce(function (int $max, string $code) use ($prefix) {
                $suffix = substr($code, strlen($prefix));

                if ($suffix === '' || ! ctype_digit($suffix) || strlen($suffix) > 5) {
                    return $max;
                }

                return max($max, (int) $suffix);
            }, 0);

        $next = $maxSequence + 1;

        return $prefix.str_pad((string) $next, max(4, strlen((string) $next)), '0', STR_PAD_LEFT);
    }

    private function isMemberCodeDuplicate(QueryException $e): bool
    {
        return $e->getCode() === '23000'
            && str_contains($e->getMessage(), 'members_business_id_member_code_unique');
    }
}
