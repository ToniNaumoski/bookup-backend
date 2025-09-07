<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
