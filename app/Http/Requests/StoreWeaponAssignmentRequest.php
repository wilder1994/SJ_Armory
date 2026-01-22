<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeaponAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'responsible_user_id' => ['required', 'exists:users,id'],
            'start_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
