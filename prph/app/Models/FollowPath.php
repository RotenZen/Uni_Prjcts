<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowPath extends Model
{
    use HasFactory;

    protected $table = 'follow_paths';
    protected $primaryKey = 'follow_id';
    public $timestamps = false; // we only have started_at

    protected $fillable = [
        'user_id',
        'post_id',
        'started_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function progress()
    {
        return $this->hasMany(PathProgress::class, 'follow_id', 'follow_id');
    }
}
