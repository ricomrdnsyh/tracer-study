<?php

namespace App\Mail;

use App\Models\BantuanLupaNim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BantuanLupaNimMail extends Mailable
{
    use Queueable, SerializesModels;

    public BantuanLupaNim $permohonan;

    /**
     * Create a new message instance.
     */
    public function __construct(BantuanLupaNim $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Tracer Study UNUJA] Bantuan Lupa NIM: ' . $this->permohonan->nama,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bantuan-lupa-nim',
            with: [
                'permohonan' => $this->permohonan,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
