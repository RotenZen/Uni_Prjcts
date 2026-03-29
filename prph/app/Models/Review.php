<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'review_id';
    public $timestamps = false; // only created_at, no updated_at

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'rating',
        'comment',
        'created_at',
    ];

    // Each review belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // A review can target a post or a resource
    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
}
