<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form (guests only).
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Handle a login attempt with throttling (5 attempts / minute / email+IP).
     */
    public function store(Request $request, RateLimiter $limiter): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();

        if ($limiter->tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $limiter->availableIn($throttleKey),
                    'minutes' => ceil($limiter->availableIn($throttleKey) / 60),
                ]),
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Resolve the account and verify the password *before* authenticating.
        // Auth::attempt() fires the Login event — and with it an 'auth.login'
        // audit row — as soon as the credentials match, so checking is_active
        // afterwards would record rejected logins as successful ones (and log
        // a phantom logout alongside). validate() fires no events.
        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        if (! $user || ! Auth::validate($credentials)) {
            $limiter->hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Deactivated accounts may not log in.
        if ($user->is_active === false) {
            $limiter->hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Please contact the administrator.',
            ]);
        }

        Auth::attempt($credentials, $remember);

        $limiter->clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Staff land on the dashboard; accounts without admin access (customers,
     * training participants) go back to the public site instead of a 403.
     */
    private function homeFor(User $user): string
    {
        return $user->can('view-admin-dashboard') ? route('admin.dashboard') : route('home');
    }

    /**
     * Log the user out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
