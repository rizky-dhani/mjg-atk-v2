<?php

namespace App\Mail;

use App\Models\AtkStockUsage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AtkStockUsageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public AtkStockUsage $stockUsage,
        public string $actionStatus, // 'submitted', 'approved', 'rejected', 'partially_approved'
        public ?User $actor = null,
        public ?string $notes = null,
        public ?string $recipientName = null,
        public ?string $viewUrl = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->actionStatus) {
            'submitted' => 'New ATK Stock Usage: '.$this->stockUsage->request_number,
            'approved' => 'ATK Stock Usage Approved: '.$this->stockUsage->request_number,
            'rejected' => 'ATK Stock Usage Rejected: '.$this->stockUsage->request_number,
            'partially_approved' => 'ATK Stock Usage Partially Approved: '.$this->stockUsage->request_number,
            default => 'ATK Stock Usage Update: '.$this->stockUsage->request_number,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.atk-stock-usage',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
