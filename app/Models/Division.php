<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['code','name','is_active'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_divisions');
    }
}
