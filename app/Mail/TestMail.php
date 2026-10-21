<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * SMTP test email (colors සහිත HTML design එකක්).
 * php artisan make:mail TestMail  කියන command එකෙන් හැදෙන Mailable class එකමයි.
 */
class TestMail extends Mailable
{
    // public properties ඔක්කොම emails/test.blade.php එකේ variables විදිහට ඉබේම පාවිච්චි කරන්න පුළුවන්
    public function __construct(
        public string $recipientName,
        public string $fromAddress,
        public string $fromName,
        public array $details = [],
    ) {
    }

    // Subject සහ From
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: 'SMTP test: ' . config('app.name'),
        );
    }

    // Email එකේ design එක තියෙන Blade view එක
    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}