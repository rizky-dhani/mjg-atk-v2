<?php

namespace App\Listeners;

use App\Models\SentEmail;
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

        $from = collect($message->getFrom())->map(fn (Address $address) => $address->toString())->implode(', ');
        $to = collect($message->getTo())->map(fn (Address $address) => $address->toString())->implode(', ');
        $cc = collect($message->getCc())->map(fn (Address $address) => $address->toString())->implode(', ');
        $bcc = collect($message->getBcc())->map(fn (Address $address) => $address->toString())->implode(', ');

        SentEmail::create([
            'from' => $from,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $message->getSubject(),
            'content_html' => $message->getHtmlBody(),
            'content_text' => $message->getTextBody(),
        ]);
    }
}
