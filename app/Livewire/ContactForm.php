<?php

namespace App\Livewire;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ContactForm extends Component
{
    /** Subject area the visitor picked via the topic chips. */
    public string $topic = 'Sales';

    #[Validate('required|string|min:2|max:120')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:160')]
    public string $company = '';

    #[Validate('nullable|string|max:40')]
    public string $teamSize = '';

    #[Validate('required|string|min:10|max:5000')]
    public string $message = '';

    #[Validate('accepted')]
    public bool $consent = false;

    /** Honeypot — real users leave this empty; bots fill it. */
    public string $website = '';

    public bool $sent = false;

    public string $sentEmail = '';

    /** Topics offered in the form, keyed for the chip UI. */
    public array $topics = ['Sales', 'Support', 'Demo', 'Partnership', 'Other'];

    public function selectTopic(string $topic): void
    {
        if (in_array($topic, $this->topics, true)) {
            $this->topic = $topic;
        }
    }

    public function send(): void
    {
        // Silently swallow bot submissions that tripped the honeypot.
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        $validated = $this->validate();

        $to = config('mail.contact_to') ?: config('mail.from.address');

        Mail::to($to)->send(new ContactMessage(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            body: $validated['message'],
            topic: $this->topic,
            company: $validated['company'] ?: null,
            teamSize: $validated['teamSize'] ?: null,
        ));

        $this->sentEmail = $validated['email'];
        $this->reset(['name', 'email', 'company', 'teamSize', 'message', 'consent']);
        $this->topic = 'Sales';
        $this->sent = true;
    }

    public function sendAnother(): void
    {
        $this->reset(['sent', 'sentEmail']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
