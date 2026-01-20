<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $salesData
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $date = now()->format('Y-m-d');
        return new Envelope(
            subject: 'Daily Sales Report - ' . $date,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'mail.daily-sales-report',
            with: [
                'date' => now()->format('Y-m-d'),
                'salesData' => $this->salesData,
                'totalRevenue' => $this->salesData['totalRevenue'] ?? 0,
                'totalItemsSold' => $this->salesData['totalItemsSold'] ?? 0,
                'products' => $this->salesData['products'] ?? [],
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
