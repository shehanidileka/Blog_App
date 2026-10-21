<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

                View::composer('layouts.app', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->get());

            $navNotifications = collect();
            $unreadCount = 0;

            if (auth()->check()) {
                try {
                    $navNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                } catch (\Throwable $e) {
                    // notifications table not migrated yet
                }
            }

            $view->with(compact('navNotifications', 'unreadCount'));
        });

        // Permissions: admins can do everything, others depend on their role's permissions
        Gate::before(function ($user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            return $user->hasPermission($ability) ? true : null;
        });

        \App\Support\Activity::boot();

        $this->applyMailSettings();
    }

    /**
     * Use the SMTP settings saved in the admin panel (if enabled).
     */
    private function applyMailSettings(): void
    {
        try {
            $s = Setting::allAsArray();
        } catch (\Throwable $e) {
            return; // settings table not migrated yet
        }

        if (($s['mail_enabled'] ?? '0') !== '1' || empty($s['mail_host'])) {
            return; // fall back to .env
        }

        $password = null;
        if (! empty($s['mail_password'])) {
            try {
                $password = Crypt::decryptString($s['mail_password']);
            } catch (\Throwable $e) {
                $password = null;
            }
        }

        $enc = $s['mail_encryption'] ?? 'tls';

        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.url'        => null,
            'mail.mailers.smtp.host'       => $s['mail_host'],
            'mail.mailers.smtp.port'       => (int) ($s['mail_port'] ?? 587),
            'mail.mailers.smtp.username'   => ($s['mail_username'] ?? '') !== '' ? $s['mail_username'] : null,
            'mail.mailers.smtp.password'   => $password,
            'mail.mailers.smtp.encryption' => $enc === 'none' ? null : $enc,
            'mail.mailers.smtp.scheme'     => $enc === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.auto_tls'   => $enc !== 'none',
            'mail.mailers.smtp.timeout'    => 15,
        ]);

        if (! empty($s['mail_from_address'])) {
            config(['mail.from.address' => $s['mail_from_address']]);
        }
        if (! empty($s['mail_from_name'])) {
            config(['mail.from.name' => $s['mail_from_name']]);
        }
    }
}