<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ErrorLogMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Throwable $exception,
        public Request $request
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ERROR] '.class_basename($this->exception).' at '.$this->request->url(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $trace = $this->exception->getTraceAsString();
        $traceLines = explode("\n", $trace);
        $traceFirst20 = implode("\n", array_slice($traceLines, 0, 20));

        return new Content(
            view: 'emails.error-log',
            with: [
                'exceptionClass' => get_class($this->exception),
                'exceptionMessage' => $this->exception->getMessage(),
                'requestMethod' => $this->request->method(),
                'requestUrl' => $this->request->fullUrl(),
                'clientIp' => $this->request->ip(),
                'stackTrace' => $traceFirst20,
                'timestamp' => now()->toDateTimeString(),
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
        return [
            Attachment::fromData(fn () => $this->buildFullDump(), 'error-dump.txt')
                ->withMime('text/plain'),
        ];
    }

    protected function buildFullDump(): string
    {
        $sections = [];

        $sections[] = '=== EXCEPTION ===';
        $sections[] = 'Class: '.get_class($this->exception);
        $sections[] = 'Message: '.$this->exception->getMessage();
        $sections[] = 'Code: '.$this->exception->getCode();
        $sections[] = '';

        $sections[] = '=== STACK TRACE ===';
        $sections[] = $this->exception->getTraceAsString();
        $sections[] = '';

        $sections[] = '=== REQUEST ===';
        $sections[] = 'Method: '.$this->request->method();
        $sections[] = 'URL: '.$this->request->fullUrl();
        $sections[] = 'IP: '.$this->request->ip();
        $sections[] = 'Input: '.json_encode($this->request->except(['password', 'password_confirmation', 'token']), JSON_PRETTY_PRINT);
        $sections[] = '';

        $sections[] = '=== HEADERS ===';
        $sections[] = json_encode($this->request->headers->all(), JSON_PRETTY_PRINT);
        $sections[] = '';

        $sections[] = '=== SESSION ===';
        $sections[] = json_encode($this->request->session()->all(), JSON_PRETTY_PRINT);
        $sections[] = '';

        $sections[] = '=== SERVER ===';
        $sections[] = json_encode($this->request->server->all(), JSON_PRETTY_PRINT);
        $sections[] = '';

        $sections[] = '=== TIMESTAMP ===';
        $sections[] = now()->toDateTimeString();

        return implode("\n", $sections);
    }
}
