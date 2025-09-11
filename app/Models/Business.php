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
        'street',
        'street_number',
        'working_hours',
        'media_type',
        'cover_image',
        'gallery',
        'status',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'gallery' => 'array',
    ];

    // Врска со User
    public function user()
    {
        return $this->belongsTo(User::class);
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
