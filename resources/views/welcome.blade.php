<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member On Desk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="hero-home">
    <nav>
        <div class="brand">
            <div class="brand-mark">M</div>
            <strong>Member On Desk</strong>
        </div>
        <a class="btn btn-light" href="{{ route('login') }}">Sign in</a>
    </nav>
    <div class="hero-copy">
        <p class="muted" style="color:#99f6e4">Gym & library management SaaS</p>
        <h1>Membership, QR attendance and payments — isolated per business.</h1>
        <p style="max-width:560px;color:rgba(255,255,255,.82)">A production-ready Laravel 12 platform for super admins and gym/library owners. Members get a unique QR. The desk scans it. Cash, UPI and gateway payments stay auditable.</p>
        <div class="hero-actions">
            <a class="btn btn-light" href="{{ route('login') }}">Open the desk</a>
        </div>
    </div>
</div>
</body>
</html>
