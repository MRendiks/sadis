<?php // app/Models/Review.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['file_id','reviewer_id','decision','notes'];
    protected $casts = ['created_at'=>'datetime'];
    public function file(){ return $this->belongsTo(FileEntry::class,'file_id'); }
    public function reviewer(){ return $this->belongsTo(User::class,'reviewer_id'); }
}
