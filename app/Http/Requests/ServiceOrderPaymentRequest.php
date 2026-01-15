<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:deposit,inspection,repair,shipping,final'],
            'method' => ['required', 'in:cash,vietqr,cod'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
