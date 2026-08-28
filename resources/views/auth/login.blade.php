<x-guest-layout title="Sign in · Member On Desk">
    <div class="guest-shell">
        <section class="guest-art">
            <div>
                <div class="brand">
                    <div class="brand-mark">M</div>
                    <strong>Member On Desk</strong>
                </div>
                <h1>Run gyms and libraries from one desk.</h1>
                <p>Register members, scan QR attendance, collect cash or UPI, and keep every business isolated in a multi-tenant SaaS.</p>
            </div>
            <p>₹499 / month · ₹4,999 / year</p>
        </section>
        <section class="guest-form">
            <div class="auth-card">
                <h2>Sign in</h2>
                <p class="muted">Use your platform or business owner account. </p>
                <form method="POST" action="{{ route('login') }}" class="form" style="margin-top:18px">
                    @csrf
                    <label>Email
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </label>
                    <label>Password
                        <input type="password" name="password" required>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-weight:500">
                        <input type="checkbox" name="remember" value="1" style="width:auto"> Remember me
                    </label>
                    <button class="btn btn-primary" type="submit">Continue</button>
                </form>
            </div>
        </section>
    </div>
</x-guest-layout>
