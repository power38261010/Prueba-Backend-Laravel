<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currency = $this->route('currency');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('currencies', 'name')->ignore($currency->id)],
            'symbol' => ['required', 'string', 'max:10', Rule::unique('currencies', 'symbol')->ignore($currency->id)],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la divisa es obligatorio.',
            'symbol.required' => 'El simbolo de la divisa es obligatorio.',
            'exchange_rate.required' => 'La tasa de cambio es obligatoria.',
            'exchange_rate.min' => 'La tasa de cambio no puede ser negativa.',
        ];
    }
}
