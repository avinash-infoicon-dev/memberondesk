<?php

namespace App\Models;

use App\Enums\SaasPlanInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaasPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'interval',
        'price',
        'currency',
        'max_members',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'interval' => SaasPlanInterval::class,
            'price' => 'decimal:2',
            'max_members' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }
}
