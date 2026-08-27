<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberQrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrCodeService
{
    public function generateFor(Member $member): MemberQrCode
    {
        return DB::transaction(function () use ($member) {
            MemberQrCode::query()
                ->where('member_id', $member->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return MemberQrCode::query()->create([
                'business_id' => $member->business_id,
                'member_id' => $member->id,
                'token' => Str::lower(Str::ulid()->toBase32()),
                'is_active' => true,
                'generated_at' => now(),
            ]);
        });
    }

    public function findActiveByToken(string $token): ?MemberQrCode
    {
        return MemberQrCode::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->first();
    }

    public function png(MemberQrCode $qrCode): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $qrCode->token,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 360,
            margin: 12,
        );

        return $builder->build()->getString();
    }
}
