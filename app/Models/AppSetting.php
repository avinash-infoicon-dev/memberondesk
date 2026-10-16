<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function appLockKey(): string
    {
        return (string) (static::query()->where('key', 'app_lock_key')->value('value')
            ?? config('mobile.app_lock_key', ''));
    }
}
