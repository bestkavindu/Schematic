<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialiteController extends Controller
{
    /**
     * OAuth providers we support. Keep in sync with config/services.php and routes.
     *
     * @var list<string>
     */
    private const PROVIDERS = ['google', 'github'];

    /**
     * Send the user off to the provider's consent screen.
     */
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the provider callback: find-or-create the user, then sign them in.
     */
    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'The sign-in session expired. Please try again.',
            ]);
        }

        $user = $this->findOrCreateUser($provider, $oauthUser);

        Auth::login($user, remember: true);

        return redirect()->intended(config('fortify.home'));
    }

    /**
     * Resolve a local user for the given OAuth identity, creating or linking as needed.
     */
    private function findOrCreateUser(string $provider, SocialiteUser $oauthUser): User
    {
        // 1) Already linked to this exact provider account.
        $user = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first();

        if ($user) {
            return $user;
        }

        // 2) A local account already owns this email — link the provider to it.
        if ($email = $oauthUser->getEmail()) {
            $user = User::query()->where('email', $email)->first();
        }

        $user ??= new User;

        $user->forceFill([
            'name' => $user->name ?: ($oauthUser->getName() ?: $oauthUser->getNickname() ?: 'New user'),
            'email' => $user->email ?: $oauthUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
            'avatar' => $oauthUser->getAvatar(),
            // OAuth identities are pre-verified by the provider.
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return $user;
    }
}
