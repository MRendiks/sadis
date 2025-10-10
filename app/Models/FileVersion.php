<?php // app/Models/FileVersion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileVersion extends Model
{
    protected $fillable = [
        'file_id','version','storage_path','storage_disk','size_bytes','mime_type','hash_sha256','uploaded_by','uploaded_at','notes'
    ];
    protected $casts = ['uploaded_at'=>'datetime','size_bytes'=>'integer','version'=>'integer'];

    public function file(){ return $this->belongsTo(FileEntry::class,'file_id'); }
    public function uploader(){ return $this->belongsTo(User::class,'uploaded_by'); }
}
