<?php

namespace App\Mail;

use App\Models\UserConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationAccountMail extends Mailable
{
    use Queueable, SerializesModels;
    public UserConfirmation $confirmation;

    /**
     * Create a new message instance.
     * @param UserConfirmation $confirmation
     */
    public function __construct(UserConfirmation $confirmation)
    {
        $this->confirmation = $confirmation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Activation account " . config('app.name') . " code",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: "emails.activation_code",
            with: [
                'code' => $this->confirmation->code,
            ],
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
