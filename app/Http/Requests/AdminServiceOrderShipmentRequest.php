<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminServiceOrderShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'carrier' => ['required', 'in:spx,ghn,manual'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'label_url' => ['nullable', 'string', 'max:2048'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'cod_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
