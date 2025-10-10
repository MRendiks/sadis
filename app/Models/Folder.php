<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'division_id','parent_id','name','slug','visibility','created_by','updated_by'
    ];
    protected $casts = ['created_at'=>'datetime','updated_at'=>'datetime'];

    public function division(){ return $this->belongsTo(Division::class); }
    public function parent(){ return $this->belongsTo(Folder::class,'parent_id'); }
    public function children(){ return $this->hasMany(Folder::class,'parent_id'); }
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function updater(){ return $this->belongsTo(User::class,'updated_by'); }
}