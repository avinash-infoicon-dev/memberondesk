<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreMemberRequest;
use App\Http\Requests\Business\UpdateMemberRequest;
use App\Models\Member;
use App\Services\MemberService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $members,
        private readonly QrCodeService $qrCodes,
    ) {
        $this->authorizeResource(Member::class, 'member');
    }

    public function index(Request $request): View
    {
        $members = Member::query()
            ->with(['activeSubscription.plan', 'activeQrCode'])
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('member_code', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('business.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('business.members.create');
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $member = $this->members->create($request->validated());

        return redirect()->route('business.members.show', $member)
            ->with('success', 'Member registered and QR code generated.');
    }

    public function show(Member $member): View
    {
        $member->load(['subscriptions.plan', 'payments', 'attendance' => fn ($q) => $q->latest('check_in_at')->limit(10), 'activeQrCode']);

        return view('business.members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        return view('business.members.edit', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $this->members->update($member, $request->validated());

        return redirect()->route('business.members.show', $member)
            ->with('success', 'Member updated.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('business.members.index')
            ->with('success', 'Member archived.');
    }

    public function regenerateQr(Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        $this->qrCodes->generateFor($member);

        return back()->with('success', 'New QR code generated.');
    }

    public function qrImage(Member $member): Response
    {
        $this->authorize('view', $member);
        $qr = $member->activeQrCode ?: $this->qrCodes->generateFor($member);

        return response($this->qrCodes->png($qr), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }
}
