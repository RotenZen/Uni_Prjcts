<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    // Table uses a custom PK name "report_id"
    protected $primaryKey = 'report_id';

    // No automatic timestamps, since the migration only has created_at
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'reason',
        'status',   // 'pending', 'reviewed', or 'action_taken'
    ];

    protected $casts = [
        'post_id' => 'integer',
        'user_id' => 'integer',
        'reason'  => 'string',
        'status'  => 'string',
    ];

    /**
     * The post that was reported.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    /**
     * The user who submitted the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
