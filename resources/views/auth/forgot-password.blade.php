@extends('layouts.auth')

@section('title', 'Forgot password — MOAUM Admin')

@section('content')
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-2xl font-bold text-moaum-charcoal">Reset your password</h1>
        <p class="mt-1 text-sm text-slate-500">Enter your email and we will send you a secure reset link.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-moaum-green/10 border border-moaum-green/30 text-moaum-green px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
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

            <x-button type="submit" class="w-full">Email password reset link</x-button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="font-semibold text-moaum-blue hover:text-moaum-blue-dark">&larr; Back to sign in</a>
        </p>
    </div>
@endsection
