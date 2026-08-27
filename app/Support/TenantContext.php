<?php

namespace App\Support;

use App\Models\Business;

class TenantContext
{
    private ?int $businessId = null;

    private bool $bypassed = false;

    public function set(?int $businessId): void
    {
        $this->businessId = $businessId;
        $this->bypassed = false;
    }

    public function id(): ?int
    {
        return $this->businessId;
    }

    public function business(): ?Business
    {
        if (! $this->businessId) {
            return null;
        }

        return Business::query()->find($this->businessId);
    }

    public function bypass(): void
    {
        $this->bypassed = true;
        $this->businessId = null;
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    public function reset(): void
    {
        $this->businessId = null;
        $this->bypassed = false;
    }

    public function hasTenant(): bool
    {
        return $this->businessId !== null && ! $this->bypassed;
    }
}
