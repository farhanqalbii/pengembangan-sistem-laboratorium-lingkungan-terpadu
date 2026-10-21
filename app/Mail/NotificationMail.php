<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'SILAB - Sistem Laboratorium Lingkungan');

        return new Envelope(
            subject: "[{$appName}] {$this->notification->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $actionUrl = $this->notification->link
            ? (str_starts_with($this->notification->link, 'http') ? $this->notification->link : url($this->notification->link))
            : url('/');

        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notification,
                'user' => $this->notification->user,
                'actionUrl' => $actionUrl,
                'appName' => config('app.name', 'SILAB - Sistem Laboratorium Lingkungan'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
