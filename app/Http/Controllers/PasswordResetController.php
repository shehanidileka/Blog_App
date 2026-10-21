<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private const EXPIRE_MINUTES = 10;
    private const MAX_ATTEMPTS   = 5;

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $data  = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower($data['email']);

        $user = User::where('email', $email)->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );
            Cache::forget($this->attemptsKey($user->email));

            try {
                Mail::to($user->email)->send(new PasswordResetCode($user->name, $code));
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors(['email' => 'We could not send the email right now. Please try again later.'])
                    ->onlyInput('email');
            }
        }

        return redirect()->route('password.code', ['email' => $email])
            ->with('status', 'If that email is registered, we have sent a 6-digit code to it.');
    }

    public function showCode(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'code'     => ['required', 'digits:6'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $email = strtolower($data['email']);
        $key   = $this->attemptsKey($email);
        $fail  = fn (string $msg) => back()->withErrors(['code' => $msg])->onlyInput('email');

        if (Cache::get($key, 0) >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $fail('Too many wrong attempts. Please request a new code.');
        }

        $row  = DB::table('password_reset_tokens')->where('email', $email)->first();
        $user = User::where('email', $email)->first();

        $valid = $row && $user
            && ! Carbon::parse($row->created_at)->addMinutes(self::EXPIRE_MINUTES)->isPast()
            && Hash::check($data['code'], $row->token);

        if (! $valid) {
            Cache::put($key, Cache::get($key, 0) + 1, now()->addMinutes(self::EXPIRE_MINUTES));

            return $fail('The code is invalid or has expired.');
        }

        $user->forceFill([
            'password'       => $data['password'], // hashed by the model cast
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        Cache::forget($key);

        return redirect()->route('login')->with('status', 'Your password has been reset. You can log in now.');
    }

    private function attemptsKey(string $email): string
    {
        return 'pwreset-attempts:' . sha1($email);
    }
}