<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
{
    public function authorize(){ return $this->user()?->hasRole('super_admin') ?? false; }
    public function rules(){
        return [
            'division_id' => ['required','exists:divisions,id'],
            'parent_id'   => ['nullable','exists:folders,id'],
            'name'        => ['required','string','max:150'],
            'slug'        => ['required','string','max:200',
                Rule::unique('folders')->where(fn($q)=>$q->where('division_id',$this->division_id))
            ],
            'visibility'  => ['required', Rule::in(['private','public'])]
        ];
    }
}
