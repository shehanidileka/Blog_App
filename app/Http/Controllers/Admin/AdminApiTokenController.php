<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AdminApiTokenController extends Controller
{
    public function index()
    {
        return view('admin.api-tokens', [
            'tokens' => PersonalAccessToken::with('tokenable')->latest('id')->get(),
            'users'  => User::orderBy('name')->get(['id', 'name', 'email']),
            'logs'   => ApiRequestLog::latest('id')->limit(30)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name'    => ['required', 'string', 'max:100'],
        ]);

        $user  = User::findOrFail($data['user_id']);
        $token = $user->createToken($data['name'], ['*']);

        Activity::log('settings.api_token_created', "Created API token “{$data['name']}” for {$user->name}");

        // the plain text token is shown only once
        return redirect()->route('admin.api-tokens.index')
            ->with('success', 'Token created. Copy it now, it will not be shown again.')
            ->with('new_token', $token->plainTextToken);
    }

    public function destroy(int $id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $label = $token->name;
        $token->delete();

        Activity::log('settings.api_token_revoked', "Revoked API token “{$label}”");

        return back()->with('success', 'Token revoked.');
    }
}