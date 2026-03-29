<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathProgress extends Model
{
    use HasFactory;

    // Table name (adjust if yours is different)
    protected $table = 'path_progress';

    // Primary key column
    protected $primaryKey = 'progress_id';

    // Disable default Laravel timestamps if not using them
    public $timestamps = false;

    // Allow mass assignment for these columns
    protected $fillable = [
        'follow_id',
        'resource_id',
        'is_completed',
        'completed_at',
        'time_to_completion',
        'created_at',
    ];

    // Relationship to FollowPath
    public function follow()
    {
        return $this->belongsTo(FollowPath::class, 'follow_id', 'follow_id');
    }
}

