<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новое сообщение с формы: ' . $this->submission->form->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission',
            with: [
                'submission' => $this->submission,
                'form' => $this->submission->form,
                'data' => $this->submission->data,
                'senderName' => $this->submission->sender_name,
                'senderEmail' => $this->submission->sender_email,
                'senderPhone' => $this->submission->sender_phone,
            ],
        );
    }
}
