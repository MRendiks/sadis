<?php // app/Models/FileEntry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileEntry extends Model
{
    protected $table = 'files';
    protected $fillable = [
        'division_id','folder_id','uploader_id','title','description',
        'original_name','storage_disk','storage_path','mime_type','size_bytes','hash_sha256',
        'status','reviewed_by','reviewed_at','review_notes','approved_at','archived_at','current_version'
    ];
    protected $casts = [
        'size_bytes'=>'integer','current_version'=>'integer',
        'reviewed_at'=>'datetime','approved_at'=>'datetime','archived_at'=>'datetime',
        'created_at'=>'datetime','updated_at'=>'datetime',
    ];

    const STATUSES = ['draft','submitted','under_review','approved','rejected','archived'];

    public function division(){ return $this->belongsTo(Division::class); }
    public function folder(){ return $this->belongsTo(Folder::class); }
    public function uploader(){ return $this->belongsTo(User::class,'uploader_id'); }
    public function reviewer(){ return $this->belongsTo(User::class,'reviewed_by'); }
    public function versions(){ return $this->hasMany(FileVersion::class,'file_id'); }
    public function reviews(){ return $this->hasMany(Review::class,'file_id'); }
}
