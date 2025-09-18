<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationStatusChanged extends Notification
{
    public $reservation;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation, $oldStatus, $newStatus)
    {
        $this->reservation = $reservation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusText = $this->getStatusText($this->newStatus);
        $actionText = $this->getActionText($this->newStatus);

        return (new MailMessage)
            ->subject("Резервација {$statusText} - RezervirajOnline.mk")
            ->greeting('Здраво!')
            ->line("Вашата резервација кај {$this->reservation->business->name} е {$statusText}.")
            ->line("**Детали на резервацијата:**")
            ->line("**Бизнис:** {$this->reservation->business->name}")
            ->line("**Датум:** {$this->reservation->date}")
            ->line("**Време:** {$this->reservation->time}")
            ->line("**Нов статус:** " . ucfirst($this->newStatus))
            ->action('Види резервации', url('/user-dashboard'))
            ->line($actionText)
            ->salutation('Со почит, Тимот на RezervirajOnline.mk');
    }

    /**
     * Get status text in Macedonian.
     */
    private function getStatusText($status)
    {
        return match($status) {
            'confirmed' => 'потврдена',
            'cancelled' => 'откажана',
            default => $status
        };
    }

    /**
     * Get action text based on status.
     */
    private function getActionText($status)
    {
        return match($status) {
            'confirmed' => 'Се радуваме што ќе ве видиме! Ако имате било какви прашања, слободно контактирајте го бизнисот.',
            'cancelled' => 'Се извинуваме за непријатноста. Можете да направите нова резервација ако сакате.',
            default => ''
        };
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