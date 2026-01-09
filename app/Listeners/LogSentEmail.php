<?php

namespace App\Listeners;

use App\Models\MonitoringEmail;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Address;

class LogSentEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $headers = $message->getHeaders();

        $actionType = $headers->get('X-Action-Type')?->getBodyAsString();
        $actionById = $headers->get('X-Action-By-Id')?->getBodyAsString();
        $actionAt = $headers->get('X-Action-At')?->getBodyAsString();

        $from = collect($message->getFrom())->map(fn (Address $address) => $address->toString())->implode(', ');
        $to = collect($message->getTo())->map(fn (Address $address) => $address->toString())->implode(', ');
        $cc = collect($message->getCc())->map(fn (Address $address) => $address->toString())->implode(', ');
        $bcc = collect($message->getBcc())->map(fn (Address $address) => $address->toString())->implode(', ');

        $statusCode = null;
        $debug = $event->sent->getDebug();
        if (preg_match('/(?:^|\\n)250\\s/', $debug)) {
            $statusCode = 250;
        } elseif (preg_match('/(?:^|\\n)(5\\d{2})\\s/', $debug, $matches)) {
            $statusCode = (int) $matches[1];
        } elseif (preg_match('/(?:^|\\n)(4\\d{2})\\s/', $debug, $matches)) {
            $statusCode = (int) $matches[1];
        }

        MonitoringEmail::create([
            'from' => $from,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $message->getSubject(),
            'content_html' => $message->getHtmlBody(),
            'content_text' => $message->getTextBody(),
            'action_type' => $actionType,
            'action_by_id' => $actionById,
            'action_at' => $actionAt ?: ($actionType ? now() : null),
            'status_code' => $statusCode,
        ]);
    }
}
