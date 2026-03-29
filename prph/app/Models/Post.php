<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';

    protected $fillable = [
        'user_id',
        'skill_id',
        'title',
        'description',
        'is_flagged',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'post_resources', 'post_id', 'resource_id')
            ->withPivot('order_number')
            ->orderBy('post_resources.order_number');
    }
    public function reports()
    {
        return $this->hasMany(\App\Models\Report::class, 'post_id', 'post_id');
    }

}

