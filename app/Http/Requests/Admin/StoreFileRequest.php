<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = Auth::user();
        // hanya super_admin & admin_arsip yang boleh upload
        return $u && (in_array($u->role, ['super_admin', 'admin_arsip']));
    }

    public function rules(): array
    {
        return [
            'division_id' => ['required','integer','exists:divisions,id'],
            'folder_id'   => ['nullable','integer','exists:folders,id'],
            'title'       => ['required','string','max:200'],
            'description' => ['nullable','string','max:1000'],
            'file'        => ['required','file','max:40960'], // 40MB contoh
        ];
    }
}
