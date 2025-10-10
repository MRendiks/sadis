<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['code','name','is_active'];
    protected $casts = ['is_active'=>'boolean'];

    public function users() { return $this->belongsToMany(User::class, 'user_divisions'); }
    public function folders() { return $this->hasMany(Folder::class); }
    public function files() { return $this->hasMany(FileEntry::class); }
}
