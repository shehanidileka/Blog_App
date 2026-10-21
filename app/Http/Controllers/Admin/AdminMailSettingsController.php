<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Support\Activity;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminMailSettingsController extends Controller
{
    public function edit()
    {
        $s = Setting::allAsArray();

        $settings = [
            'enabled'      => ($s['mail_enabled'] ?? '0') === '1',
            'host'         => $s['mail_host'] ?? '',
            'port'         => $s['mail_port'] ?? 587,
            'encryption'   => $s['mail_encryption'] ?? 'tls',
            'username'     => $s['mail_username'] ?? '',
            'has_password' => ! empty($s['mail_password']),
            'from_address' => $s['mail_from_address'] ?? config('mail.from.address'),
            'from_name'    => $s['mail_from_name'] ?? config('mail.from.name'),
        ];

        return view('admin.mail', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'host'         => ['required_if:enabled,1', 'nullable', 'string', 'max:255'],
            'port'         => ['required_if:enabled,1', 'nullable', 'integer', 'between:1,65535'],
            'encryption'   => ['required', 'in:tls,ssl,none'],
            'username'     => ['nullable', 'string', 'max:255'],
            'password'     => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name'    => ['required', 'string', 'max:100'],
        ]);

        Setting::put('mail_enabled', $request->boolean('enabled') ? '1' : '0');
        Setting::put('mail_host', $data['host'] ?? '');
        Setting::put('mail_port', (string) ($data['port'] ?? 587));
        Setting::put('mail_encryption', $data['encryption']);
        Setting::put('mail_username', $data['username'] ?? '');
        Setting::put('mail_from_address', $data['from_address']);
        Setting::put('mail_from_name', $data['from_name']);

        // Password: keep the old one if the field is left empty
        if (! empty($data['password'])) {
            Setting::put('mail_password', Crypt::encryptString($data['password']));
        } elseif ($request->boolean('clear_password')) {
            Setting::forget('mail_password');
        }

        Activity::log('settings.mail_updated', 'Mail settings changed (' . ($request->boolean('enabled') ? 'custom SMTP on, host ' . ($data['host'] ?? '') : 'custom SMTP off') . ')');

        return redirect()->route('admin.settings.mail')->with('success', 'Mail settings saved.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $to = $request->input('test_email');

        try {
            Mail::to($to)->send(new TestMail(
                recipientName: Str::before($to, '@'),
                fromAddress:   (string) config('mail.from.address'),
                fromName:      (string) config('mail.from.name'),
                details: [
                    'Mailer'     => config('mail.default'),
                    'Host'       => config('mail.mailers.smtp.host'),
                    'Port'       => config('mail.mailers.smtp.port'),
                    'Encryption' => config('mail.mailers.smtp.encryption') ?: 'none',
                    'From'       => config('mail.from.address'),
                    'Sent at'    => now()->toDateTimeString(),
                ],
            ));
        } catch (\Throwable $e) {
            return back()
                ->withInput($request->only('test_email'))
                ->with('error', 'Could not send the email: ' . Str::limit($e->getMessage(), 300));
        }

        if (config('mail.default') === 'log') {
            return back()
                ->withInput($request->only('test_email'))
                ->with('success', 'Custom SMTP is disabled, so the mail was only written to storage/logs/laravel.log (log driver).');
        }

        return back()
            ->withInput($request->only('test_email'))
            ->with('success', 'Test email sent to ' . $to . '. Check the inbox (and spam folder).');
    }
}