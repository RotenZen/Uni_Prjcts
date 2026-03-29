<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $primaryKey = 'resource_id';

    protected $fillable = [
        'type',
        'title',
        'url',
        'domain',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_resources', 'resource_id', 'post_id')
            ->withPivot('order_number');
    }
}
