<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google login is not set up yet. Add GOOGLE_CLIENT_ID in .env']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        $user = User::where('google_id', $google->getId())
            ->orWhere('email', $google->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $google->getId(),
                'avatar'    => $user->getRawOriginal('avatar') ?: $google->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'name'              => $google->getName(),
                'email'             => $google->getEmail(),
                'google_id'         => $google->getId(),
                'avatar'            => $google->getAvatar(),
                'password'          => Str::random(32),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }
}