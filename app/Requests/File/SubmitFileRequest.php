<?php // app/Http/Requests/File/SubmitFileRequest.php
namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitFileRequest extends FormRequest
{
    public function authorize(){ return $this->user()?->hasAnyRole(['user','admin','super_admin']) ?? false; }
    public function rules(){
        return [
            'division_id' => ['required','exists:divisions,id'],
            'folder_id'   => ['nullable','exists:folders,id'],
            'title'       => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'file'        => ['required','file','max:51200'], // 50MB
        ];
    }
}
