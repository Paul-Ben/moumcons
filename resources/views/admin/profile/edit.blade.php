{{-- Admin → My profile. --}}
<x-layouts.admin title="My profile">
    <div class="space-y-6 max-w-3xl">
        <x-admin.page-header title="My profile" :description="'Signed in as '.$user->email.' · '.($user->getRoleNames()->implode(', ') ?: 'no role')" />

        <form method="POST" action="{{ route('admin.profile.update') }}" class="card space-y-4">
            @csrf
            @method('PUT')
            <h2 class="font-semibold text-moaum-charcoal">Details</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.input name="name" label="Full name" :value="$user->name" required />
                <x-admin.input name="email" type="email" label="Email" :value="$user->email" required />
                <x-admin.input name="phone" label="Phone" :value="$user->phone" />
            </div>
            <div class="text-right"><button type="submit" class="btn-primary text-sm">Save details</button></div>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="card space-y-4">
            @csrf
            @method('PUT')
            <h2 class="font-semibold text-moaum-charcoal">Change password</h2>
            <x-admin.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.input name="password" type="password" label="New password" required autocomplete="new-password"
                               hint="At least 10 characters with upper and lower case letters and a number." />
                <x-admin.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
            </div>
            <div class="text-right"><button type="submit" class="btn-primary text-sm">Change password</button></div>
        </form>
    </div>
</x-layouts.admin>
