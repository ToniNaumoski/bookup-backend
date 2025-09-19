<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationMessage extends Model
{
    protected $fillable = [
        'reservation_id',
        'sender_id',
        'message',
        'sender_type'
    ];

    /**
     * Get the reservation that owns the message.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
