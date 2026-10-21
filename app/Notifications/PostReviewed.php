<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PostReviewed extends Notification
{
    public function __construct(
        public Post $post,
        public bool $approved,
        public ?string $reason = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        // 'database' first, so the in-app notification is saved even if the email fails
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->approved ? 'Your post has been approved 🎉' : 'Your post was not approved')
            ->greeting('Hi ' . Str::before($notifiable->name, ' ') . ',');

        if ($this->approved) {
            return $mail
                ->line('Good news! Your post "' . $this->post->title . '" has been approved and is now live on MyBlog.')
                ->action('View your post', route('posts.show', $this->post));
        }

        $mail->line('Your post "' . $this->post->title . '" was not approved.');

        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail
            ->line('You can edit the post and submit it again.')
            ->action('Edit post', route('dashboard.posts.edit', $this->post));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'slug'    => $this->post->slug,
            'title'   => $this->post->title,
            'status'  => $this->approved ? 'approved' : 'rejected',
            'reason'  => $this->reason,
            'message' => $this->approved
                ? 'Your post "' . Str::limit($this->post->title, 50) . '" has been approved and is now live.'
                : 'Your post "' . Str::limit($this->post->title, 50) . '" was not approved.',
        ];
    }
}