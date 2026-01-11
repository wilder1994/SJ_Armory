<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeaponStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool)($this->user()?->isAdmin());
    }

    public function rules(): array
    {
        return [
            'operational_status' => [
                'required',
                'in:in_armory,assigned,in_transit,in_maintenance,seized_or_withdrawn,decommissioned',
            ],
        ];
    }
}
