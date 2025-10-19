<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessReviewEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $admin;
    public $action;
    public $adminMessage;
    public $remarks;

    /**
     * Create a new message instance.
     */
    public function __construct($business, $admin, $action, $adminMessage = null, $remarks = null)
    {
        $this->business = $business;
        $this->admin = $admin;
        $this->action = $action;
        $this->adminMessage = $adminMessage;
        $this->remarks = $remarks;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->action) {
            'approve' => 'Вашата апликација за бизнис е одобрена - RezervirajOnline.мк',
            'reject' => 'Вашата апликација за бизнис е одбиена - RezervirajOnline.мк',
            'request_changes' => 'Потребни се промени во вашата апликација за бизнис - RezervirajOnline.мк',
            default => 'Ажурирање за вашата апликација за бизнис - RezervirajOnline.мк'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.business-review',
            with: [
                'business' => $this->business,
                'admin' => $this->admin,
                'action' => $this->action,
                'adminMessage' => $this->adminMessage,
                'remarks' => $this->remarks,
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