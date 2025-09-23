<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationCancelledByUser extends Notification
{
    public $reservation;
    public $action; // 'cancelled' or 'deleted'

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation, $action = 'cancelled')
    {
        $this->reservation = $reservation;
        $this->action = $action;
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
        $actionText = $this->getActionText();
        $subject = $this->action === 'deleted' ? 'Резервација избришана' : 'Резервација откажана';

        $message = (new MailMessage)
            ->subject("{$subject} - RezervirajOnline.mk")
            ->greeting('Здраво!')
            ->line("Корисникот {$this->reservation->user->name} ја {$actionText} резервацијата.")
            ->line("**Детали на резервацијата:**")
            ->line("**Корисник:** {$this->reservation->user->name}")
            ->line("**Емаил:** {$this->reservation->user->email}")
            ->line("**Телефон:** {$this->reservation->user->phone}")
            ->line("**Датум:** {$this->reservation->date}")
            ->line("**Време:** {$this->reservation->time}");

        // Include custom message if provided
        if ($this->reservation->message) {
            $message->line("**Порака од {$this->reservation->user->name}:**")
                   ->line($this->reservation->message);
        }

        $message->action('Види резервации', url('https://rezervirajonline.mk/'))
                ->line('Можете да контактирате со корисникот ако е потребно.')
                ->salutation('Со почит, Тимот на RezervirajOnline.mk');

        return $message;
    }

    /**
     * Get action text in Macedonian.
     */
    private function getActionText()
    {
        return $this->action === 'deleted' ? 'избриша' : 'откажа';
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