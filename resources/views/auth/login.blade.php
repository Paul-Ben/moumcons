<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign in') — MOAUM Admin</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Auth layout — Design System §27 (auth screens): centered card on charcoal backdrop --}}
<body class="font-sans bg-slate-50 antialiased">
    <div class="min-h-screen flex items-center justify-center bg-moaum-charcoal px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Brand --}}
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="w-10 h-10 bg-moaum-red rounded-lg flex items-center justify-center font-bold text-white text-lg">M</div>
                <span class="font-display text-white text-xl font-bold tracking-tight">MOAUM Consultancy</span>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h1 class="text-2xl font-bold text-moaum-charcoal">Admin sign in</h1>
                <p class="mt-1 text-sm text-slate-500">Authorized staff only. All attempts are logged.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-lg bg-moaum-green/10 border border-moaum-green/30 text-moaum-green px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="username" autofocus required
                               value="{{ old('email') }}"
                               class="form-input @error('email') border-moaum-red @enderror">
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="form-input @error('password') border-moaum-red @enderror">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-moaum-red focus:ring-moaum-blue">
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-moaum-blue hover:text-moaum-blue-dark">Forgot password?</a>
                    </div>

                    <x-button type="submit" class="w-full">Sign in</x-button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-white transition">&larr; Back to website</a>
            </p>
        </div>
    </div>
</body>
</html>
