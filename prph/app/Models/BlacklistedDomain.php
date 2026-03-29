<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedDomain extends Model
{
    protected $table = 'blacklisted_domains';
    protected $primaryKey = 'domain';
    public $incrementing = false; // because primary key is a string
    protected $keyType = 'string';

    protected $fillable = [
        'domain',
        'reason',
        'added_at',
    ];

    public $timestamps = false; // we have custom timestamp column
}
