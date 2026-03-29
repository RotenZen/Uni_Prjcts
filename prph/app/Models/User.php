<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// ✅ Add this import so we can use Sanctum tokens
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    /**
     * Traits provide extra functionality to the model.
     * - HasApiTokens → allows user model to issue/handle API tokens with Sanctum
     * - HasFactory → lets you use Laravel factories for testing/seeders
     * - Notifiable → allows sending notifications to the user (emails, etc.)
     */

    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id'; //without this line got error testing login api


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'u_name', //users display name
        'email', //unique
        'password', //hashed
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', //automatically hashed when saving
        ];
    }
}
