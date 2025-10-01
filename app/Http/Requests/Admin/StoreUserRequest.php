<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:150'],
            'email' => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'roles' => ['required','array'],
            'roles.*' => ['string','in:super_admin,admin_arsip,user_bidang,user_tamu'],
            'divisions' => ['array'],
            'divisions.*' => ['integer','exists:divisions,id'],
            'primary_division_id' => ['nullable','integer','exists:divisions,id'],
        ];
    }
}
