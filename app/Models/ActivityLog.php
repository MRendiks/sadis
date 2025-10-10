<?php // app/Models/ActivityLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'subject_type','subject_id','action','causer_id','properties','ip_address','user_agent','created_at'
    ];
    protected $casts = ['created_at'=>'datetime','properties'=>'array'];
}
