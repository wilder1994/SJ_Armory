<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeaponCustodyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool)($this->user()?->isAdmin());
    }

    public function rules(): array
    {
        return [
            'custodian_user_id' => ['required', 'exists:users,id'],
            'start_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
