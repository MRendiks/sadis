<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasRolesAndDivisions;

class User extends Authenticatable
{
    use Notifiable, HasRolesAndDivisions;

    protected $fillable = [
        'name','email','password','primary_division_id','is_active'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];


}
