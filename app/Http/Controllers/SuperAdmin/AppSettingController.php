<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppSettingController extends Controller
{
    public function edit(): View
    {
        return view('super-admin.settings.edit', ['appLockKey' => AppSetting::appLockKey()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_lock_key' => ['present', 'nullable', 'string', 'max:255'],
        ]);

        AppSetting::query()->updateOrCreate(
            ['key' => 'app_lock_key'],
            ['value' => $data['app_lock_key'] ?? ''],
        );

        return redirect()->route('super-admin.settings.edit')
            ->with('success', 'App lock key updated.');
    }
}
