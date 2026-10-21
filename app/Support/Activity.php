<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

/**
 * Activity log.
 *
 *   Activity::log('mail.updated', 'Mail settings were changed');     // write one entry by hand
 *
 * Activity::boot() (called from AppServiceProvider) also logs automatically:
 *   - create / update / delete of Post, Category, Comment, Role, Permission, User
 *   - login, logout, failed login
 */
class Activity
{
    /** Models that are logged automatically => label shown in the log */
    private const MODELS = [
        Post::class       => 'post',
        Category::class   => 'category',
        Comment::class    => 'comment',
        Role::class       => 'role',
        Permission::class => 'permission',
        User::class       => 'user',
    ];

    /** Never stored, even as "changed" */
    private const HIDDEN = ['password', 'remember_token', 'google_id'];

    /** Changes to only these fields are not worth logging (views counter, generated SEO fields ...) */
    private const NOISE = ['updated_at', 'created_at', 'views', 'meta_image', 'meta_title', 'meta_description', 'meta_keywords'];

    public static function log(string $action, string $description, ?Model $subject = null, array $properties = [], ?string $userName = null): void
    {
        try {
            $user = auth()->user();

            ActivityLog::create([
                'user_id'      => $user?->id,
                'user_name'    => $userName ?? $user?->name,
                'action'       => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id'   => $subject?->getKey(),
                'description'  => Str::limit($description, 500, '…'),
                'properties'   => $properties ?: null,
                'ip_address'   => app()->runningInConsole() ? null : request()->ip(),
                'user_agent'   => app()->runningInConsole() ? null : Str::limit((string) request()->userAgent(), 250, ''),
            ]);
        } catch (\Throwable $e) {
            // The log must never break the real action (e.g. table not migrated yet)
            report($e);
        }
    }

    public static function boot(): void
    {
        foreach (self::MODELS as $class => $label) {
            $class::created(function (Model $m) use ($label) {
                self::log("$label.created", ucfirst($label) . ' created: ' . self::title($m), $m);
            });

            $class::updated(function (Model $m) use ($label) {
                $changes = self::changes($m);
                if (! $changes) {
                    return;
                }

                [$action, $text] = ["$label.updated", ucfirst($label) . ' updated: ' . self::title($m)];

                if ($label === 'post' && isset($changes['status'])) {
                    $new = $changes['status']['new'];
                    $action = in_array($new, ['approved', 'rejected', 'pending'], true) ? "post.$new" : $action;
                    $text = 'Post ' . $new . ': ' . self::title($m);
                }

                if ($label === 'user' && isset($changes['role'])) {
                    $action = 'user.role_changed';
                    $text = 'Role of ' . self::title($m) . ' changed: ' . $changes['role']['old'] . ' → ' . $changes['role']['new'];
                }

                self::log($action, $text, $m, $changes);
            });

            $class::deleted(function (Model $m) use ($label) {
                self::log("$label.deleted", ucfirst($label) . ' deleted: ' . self::title($m), $m);
            });
        }

        Event::listen(Login::class, function (Login $e) {
            self::log('user.login', 'Logged in', $e->user, [], $e->user->name);
        });

        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user) {
                self::log('user.logout', 'Logged out', $e->user, [], $e->user->name);
            }
        });

        Event::listen(Failed::class, function (Failed $e) {
            $email = (string) ($e->credentials['email'] ?? 'unknown');
            self::log('user.login_failed', 'Failed login attempt for ' . $email, null, [], $email);
        });
    }

    /** What changed on a model: ['field' => ['old' => .., 'new' => ..]] */
    private static function changes(Model $m): array
    {
        $out = [];

        foreach ($m->getChanges() as $field => $new) {
            if (in_array($field, self::NOISE, true)) {
                continue;
            }

            if (in_array($field, self::HIDDEN, true)) {
                $out[$field] = ['old' => '••••', 'new' => '••••'];
                continue;
            }

            $out[$field] = [
                'old' => self::short($m->getOriginal($field)),
                'new' => self::short($new),
            ];
        }

        return $out;
    }

    private static function short(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::limit(trim(strip_tags(is_scalar($value) ? (string) $value : json_encode($value))), 80, '…');
    }

    private static function title(Model $m): string
    {
        return match (true) {
            $m instanceof Post    => $m->title ?: '#' . $m->id,
            $m instanceof Comment => '"' . Str::limit(strip_tags((string) $m->body), 50) . '" (post #' . $m->post_id . ')',
            $m instanceof User    => $m->name . ' <' . $m->email . '>',
            default               => $m->name ?? ('#' . $m->getKey()),
        };
    }
}