@props(['title' => null, 'heading' => 'Dashboard'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
<div class="app-shell">
    @include('layouts.partials.sidebar')
    <div class="main">
        <header class="topbar">
            <div>
                <button class="mobile-toggle" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')">Menu</button>
                <strong>{{ $heading ?? 'Dashboard' }}</strong>
            </div>
            <div class="user-chip">
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <div class="muted">{{ auth()->user()->role->label() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm" type="submit">Logout</button>
                </form>
            </div>
        </header>
        <div class="page">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
