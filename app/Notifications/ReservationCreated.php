<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationCreated extends Notification
{
    public $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load('messages.sender');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Нова резервација - RezervirajOnline.mk')
            ->greeting('Здраво!')
            ->line("Имате нова резервација од корисникот {$this->reservation->user->name}.")
            ->line("**Детали на резервацијата:**")
            ->line("**Корисник:** {$this->reservation->user->name}")
            ->line("**Емаил:** {$this->reservation->user->email}")
            ->line("**Телефон:** {$this->reservation->user->phone}")
            ->line("**Датум:** {$this->reservation->date}")
            ->line("**Време:** {$this->reservation->time}")
            ->line("**Статус:** " . ucfirst($this->reservation->status));

        // Include initial message if exists
        $initialMessage = $this->reservation->messages->where('sender_type', 'user')->first();
        if ($initialMessage) {
            $mail->line("**Порака од корисникот:**")
                 ->line("\"{$initialMessage->message}\"");
        }

        $mail->action('Види резервации', url('/business-dashboard'))
             ->line('Ве молиме да ја потврдите или откажете резервацијата што е можно поскоро.')
             ->salutation('Со почит, Тимот на RezervirajOnline.mk');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}