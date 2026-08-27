<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        string $action,
        ?Model $auditable = null,
        array $old = [],
        array $new = [],
        ?User $user = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $user ??= $request?->user();

        return AuditLog::query()->create([
            'business_id' => $user?->business_id ?? app(TenantContext::class)->id(),
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
