<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class WeeklySummaryMail extends Mailable
{
    public function __construct(
        public string $adminName,
        public array $stats,
        public Collection $topPosts,
        public CarbonInterface $periodFrom,
        public CarbonInterface $periodTo,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' weekly summary: ' . $this->periodFrom->format('M d') . ' – ' . $this->periodTo->format('M d'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.weekly-summary');
    }
}