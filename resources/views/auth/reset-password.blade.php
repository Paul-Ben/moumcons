@extends('layouts.auth')

@section('title', 'Choose new password — MOAUM Admin')

@section('content')
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-2xl font-bold text-moaum-charcoal">Choose a new password</h1>
        <p class="mt-1 text-sm text-slate-500">Minimum 8 characters, with letters and numbers.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="form-label">Email address</label>
                <input id="email" name="email" type="email" required readonly
                       value="{{ $email ?? old('email') }}"
                       class="form-input bg-slate-50 text-slate-500">
            </div>

            <div>
                <label for="password" class="form-label">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                       class="form-input @error('password') border-moaum-red @enderror">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                       class="form-input">
            </div>

            <x-button type="submit" class="w-full">Reset password</x-button>
        </form>
    </div>
@endsection
