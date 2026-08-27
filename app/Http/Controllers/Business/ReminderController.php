<?php

namespace App\Http\Controllers\Business;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\NotificationLog;
use App\Models\Subscription;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(): View
    {
        $logs = NotificationLog::query()->with('member')->latest()->paginate(30);
        $members = Member::query()->orderBy('name')->get();

        return view('business.reminders.index', compact('logs', 'members'));
    }

    public function store(Request $request, WhatsAppService $whatsApp): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'type' => ['required', 'in:payment_pending,subscription_expiring,subscription_expired,custom'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $member = Member::query()->findOrFail($data['member_id']);
        $type = NotificationType::from($data['type']);
        $subscription = $member->subscriptions()->latest('id')->first();
        $message = $data['message'] ?: $whatsApp->reminderMessage($member, $type, $subscription);

        $whatsApp->queueReminder($member, $type, $message);

        return back()->with('success', 'WhatsApp reminder queued.');
    }

    public function expiring(WhatsAppService $whatsApp): RedirectResponse
    {
        $queued = 0;
        Subscription::query()->with('member')->expiring(7)->get()->each(function (Subscription $subscription) use ($whatsApp, &$queued) {
            $member = $subscription->member;
            if (! $member?->phone) {
                return;
            }
            $whatsApp->queueReminder(
                $member,
                NotificationType::SubscriptionExpiring,
                $whatsApp->reminderMessage($member, NotificationType::SubscriptionExpiring, $subscription)
            );
            $queued++;
        });

        return back()->with('success', "Queued {$queued} expiry reminders.");
    }
}
