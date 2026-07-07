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
        public string $exceptionClass,
        public string $exceptionMessage,
        public int|string $exceptionCode,
        public string $stackTrace,
        public string $requestMethod,
        public string $requestUrl,
        public string $clientIp,
        public string $requestInput,
        public array $requestHeaders,
        public array $requestSession,
        public array $requestServer,
        public string $timestamp,
    ) {}

    /**
     * Build from an exception and request, extracting only serializable data.
     */
    public static function fromThrowable(Throwable $exception, Request $request): static
    {
        $traceLines = explode("\n", $exception->getTraceAsString());

        return new static(
            exceptionClass: get_class($exception),
            exceptionMessage: $exception->getMessage(),
            exceptionCode: $exception->getCode(),
            stackTrace: implode("\n", array_slice($traceLines, 0, 20)),
            requestMethod: $request->method(),
            requestUrl: $request->fullUrl(),
            clientIp: $request->ip(),
            requestInput: json_encode($request->except(['password', 'password_confirmation', 'token']), JSON_PRETTY_PRINT),
            requestHeaders: $request->headers->all(),
            requestSession: $request->session()->all(),
            requestServer: $request->server->all(),
            timestamp: now()->toDateTimeString(),
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ERROR] '.$this->exceptionClass.' at '.$this->requestUrl,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.error-log',
            with: [
                'exceptionClass' => $this->exceptionClass,
                'exceptionMessage' => $this->exceptionMessage,
                'requestMethod' => $this->requestMethod,
                'requestUrl' => $this->requestUrl,
                'clientIp' => $this->clientIp,
                'stackTrace' => $this->stackTrace,
                'timestamp' => $this->timestamp,
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
        $fullDump = <<<DUMP
            === EXCEPTION ===
            Class: {$this->exceptionClass}
            Message: {$this->exceptionMessage}
            Code: {$this->exceptionCode}

            === STACK TRACE ===
            {$this->stackTrace}

            === REQUEST ===
            Method: {$this->requestMethod}
            URL: {$this->requestUrl}
            IP: {$this->clientIp}
            Input: {$this->requestInput}

            === HEADERS ===
            {$this->formatJson($this->requestHeaders)}

            === SESSION ===
            {$this->formatJson($this->requestSession)}

            === SERVER ===
            {$this->formatJson($this->requestServer)}

            === TIMESTAMP ===
            {$this->timestamp}
            DUMP;

        return [
            Attachment::fromData(fn () => $fullDump, 'error-dump.txt')
                ->withMime('text/plain'),
        ];
    }

    protected function formatJson(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
