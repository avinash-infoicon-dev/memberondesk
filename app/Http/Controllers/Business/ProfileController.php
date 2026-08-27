<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\UpdateBusinessProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('business.profile.edit', [
            'business' => auth()->user()->business,
        ]);
    }

    public function update(UpdateBusinessProfileRequest $request): RedirectResponse
    {
        $business = $request->user()->business;
        $this->authorize('update', $business);

        $business->update([
            ...$request->safe()->except('whatsapp_enabled'),
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
        ]);

        return back()->with('success', 'Business profile updated.');
    }
}
