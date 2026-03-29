<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $primaryKey = 'skill_id';

    protected $fillable = [
        's_name',
        'description',
    ];

    public $timestamps = false; // because your migration only has created_at
}
