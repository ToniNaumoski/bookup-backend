<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\BusinessCreated;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'main_category',
        'sub_category',
        'city',
        'municipality',
        'street',
        'street_number',
        'working_hours',
        'media_type',
        'cover_image',
        'gallery',
        'status',
        'admin_remarks',
        'admin_messages',
        'read_messages',
        'review_status',
        'last_reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'gallery' => 'array',
        'admin_messages' => 'array',
        'read_messages' => 'array',
        'last_reviewed_at' => 'datetime',
    ];

    // Врска со User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservations for the business.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the admin user who reviewed this business.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Business $business) {
            // Broadcast business created event
            event(new BusinessCreated($business));
        });
    }
}
