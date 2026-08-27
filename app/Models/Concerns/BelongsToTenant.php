<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Models\Scopes\TenantScope;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            if (! empty($model->business_id)) {
                return;
            }

            $tenantId = app(TenantContext::class)->id();

            if ($tenantId) {
                $model->business_id = $tenantId;
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
