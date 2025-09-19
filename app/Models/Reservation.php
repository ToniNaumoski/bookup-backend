<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\ReservationCreated;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'business_id',
        'date',
        'time',
        'status',
        'capacity'
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business that owns the reservation.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the messages for this reservation.
     */
    public function messages()
    {
        return $this->hasMany(ReservationMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Reservation $reservation) {
            // Broadcast reservation created event
            event(new ReservationCreated($reservation));
        });
    }
}