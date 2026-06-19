<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string       $senderName   Name the visitor entered.
     * @param  string       $senderEmail  Email the visitor entered (used as Reply-To).
     * @param  string       $body         The message body.
     * @param  string       $topic        Subject area chosen via the topic chips.
     * @param  string|null  $company      Optional company name.
     * @param  string|null  $teamSize     Optional team size bracket.
     */
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $body,
        public string $topic = 'General',
        public ?string $company = null,
        public ?string $teamSize = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.$this->topic.'] Contact form: '.$this->senderName,
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact',
        );
    }
}
