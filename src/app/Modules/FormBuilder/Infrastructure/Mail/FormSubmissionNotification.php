<?php

namespace App\Modules\FormBuilder\Infrastructure\Mail;

use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public Form $form,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новое сообщение с формы: ' . $this->form->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'form-manager::emails.form-submission',
            with: [
                'submission' => $this->submission,
                'form' => $this->form,
                'data' => $this->submission->data,
                'senderName' => $this->getSenderName(),
                'senderEmail' => $this->getSenderEmail(),
                'senderPhone' => $this->getSenderPhone(),
            ],
        );
    }

    private function getSenderName(): string
    {
        $data = $this->submission->data;
        foreach (['name','Name','NAME','fullname','FullName','FULLNAME','fio','FIO','Fio'] as $f) {
            if (!empty($data[$f])) return (string) $data[$f];
        }
        if (!empty($data['email'])) return (string) $data['email'];
        if (!empty($data['phone'])) return (string) $data['phone'];
        return 'Аноним';
    }

    private function getSenderEmail(): ?string
    {
        $data = $this->submission->data;
        foreach (['email','Email','EMAIL','e-mail','mail'] as $f) {
            if (!empty($data[$f])) return (string) $data[$f];
        }
        return null;
    }

    private function getSenderPhone(): ?string
    {
        $data = $this->submission->data;
        foreach (['phone','Phone','PHONE','telephone','mobile','tel'] as $f) {
            if (!empty($data[$f])) return (string) $data[$f];
        }
        return null;
    }
}