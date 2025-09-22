<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUserRating extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'reservation_id',
        'stars',
        'message',
    ];

    protected $casts = [
        'stars' => 'integer',
    ];

    /**
     * Get the business that owns the rating.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that owns the rating.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservation that owns the rating.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
