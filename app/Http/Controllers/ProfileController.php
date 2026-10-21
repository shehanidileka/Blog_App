<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        $stats = [
            'posts'     => $user->posts()->count(),
            'published' => $user->posts()->approved()->count(),
            'views'     => (int) $user->posts()->sum('views'),
        ];

        return view('profile.edit', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio'     => ['nullable', 'string', 'max:300'],
            'website' => ['nullable', 'url', 'max:255'],
            'avatar'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->google_id) {
            unset($data['email']);
        }

        unset($data['avatar']);
        if ($request->hasFile('avatar')) {
            $this->deleteLocalAvatar($user);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        $this->deleteLocalAvatar($user);
        $user->update(['avatar' => null]);

        return redirect()->route('profile.edit')->with('success', 'Profile photo removed.');
    }

    public function password(Request $request)
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        if (! $user->google_id) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validateWithBag('password', $rules);

        $user->update(['password' => $request->input('password')]);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully.');
    }

    private function deleteLocalAvatar(User $user): void
    {
        $raw = $user->getRawOriginal('avatar');

        if ($raw && ! Str::startsWith($raw, ['http://', 'https://'])) {
            Storage::disk('public')->delete($raw);
        }
    }
}