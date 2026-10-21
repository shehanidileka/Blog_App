<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummaryMail;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklySummary extends Command
{
    protected $signature = 'report:weekly
                            {--days=7 : How many days the summary covers}
                            {--to= : Send to this email only (for testing) instead of all admins}';

    protected $description = 'Email the admins a weekly summary: new posts, comments, reactions, users and the top posts';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $to   = now();
        $from = now()->subDays($days);
        $prevFrom = now()->subDays($days * 2);

        // value for this period + value for the period before it (to show ▲ / ▼)
        $count = fn (string $model, $table = null) => [
            'value' => $model::where('created_at', '>=', $from)->count(),
            'prev'  => $model::whereBetween('created_at', [$prevFrom, $from])->count(),
        ];

        $stats = [
            'posts'     => [
                'value' => Post::approved()->where('created_at', '>=', $from)->count(),
                'prev'  => Post::approved()->whereBetween('created_at', [$prevFrom, $from])->count(),
            ],
            'comments'  => $count(Comment::class),
            'reactions' => $count(Reaction::class),
            'users'     => $count(User::class),
            'pending'   => Post::pending()->count(),                 // right now
            'views'     => (int) Post::approved()->sum('views'),     // all time (the site keeps one counter per post)
        ];

        // Top 3 posts of the period = most comments + reactions received in the period
        $topPosts = Post::approved()
            ->withCount([
                'comments as week_comments'  => fn ($q) => $q->where('created_at', '>=', $from),
                'reactions as week_reactions' => fn ($q) => $q->where('created_at', '>=', $from),
            ])
            ->with('user')
            ->get()
            ->map(function ($post) {
                $post->score = $post->week_comments + $post->week_reactions;
                return $post;
            })
            ->filter(fn ($post) => $post->score > 0)
            ->sortByDesc('score')
            ->take(3)
            ->values();

        $recipients = $this->option('to')
            ? collect([(object) ['name' => 'Admin', 'email' => $this->option('to')]])
            : User::where('role', 'admin')->whereNotNull('email')->get();

        if ($recipients->isEmpty()) {
            $this->warn('No admin users with an email address were found.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($recipients as $admin) {
            try {
                Mail::to($admin->email)->send(new WeeklySummaryMail($admin->name, $stats, $topPosts, $from, $to));
                $sent++;
                $this->info("Sent to {$admin->email}");
            } catch (\Throwable $e) {
                $this->error("Could not send to {$admin->email}: " . $e->getMessage());
            }
        }

        Activity::log(
            'settings.weekly_summary_sent',
            "Weekly summary sent to $sent of {$recipients->count()} admin(s)",
            null,
            [],
            'Scheduler'
        );

        return $sent > 0 ? self::SUCCESS : self::FAILURE;
    }
}