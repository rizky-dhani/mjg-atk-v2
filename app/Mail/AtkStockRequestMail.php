<?php

namespace App\Mail;

use App\Models\AtkStockRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AtkStockRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public AtkStockRequest $stockRequest,
        public string $actionStatus, // 'submitted', 'approved', 'rejected', 'partially_approved'
        public ?User $actor = null,
        public ?string $notes = null,
        public ?string $recipientName = null,
        public ?string $viewUrl = null,
        public bool $isApprover = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->actionStatus) {
            'submitted' => 'New ATK Stock Request: '.$this->stockRequest->request_number,
            'approved' => 'ATK Stock Request Approved: '.$this->stockRequest->request_number,
            'rejected' => 'ATK Stock Request Rejected: '.$this->stockRequest->request_number,
            'partially_approved' => 'ATK Stock Request Partially Approved: '.$this->stockRequest->request_number,
            default => 'ATK Stock Request Update: '.$this->stockRequest->request_number,
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
            view: 'emails.atk-stock-request',
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
