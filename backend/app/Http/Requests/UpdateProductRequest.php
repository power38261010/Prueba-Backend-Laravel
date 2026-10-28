<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT = actualizacion completa: todos los campos son requeridos.
 * Si se desea actualizacion parcial, se debe agregar un endpoint PATCH separado.
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'tax_cost' => ['required', 'numeric', 'min:0'],
            'manufacturing_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'description.required' => 'La descripcion del producto es obligatoria.',
            'price.required' => 'El precio es obligatorio.',
            'price.min' => 'El precio no puede ser negativo.',
            'currency_id.required' => 'La divisa base es obligatoria.',
            'currency_id.exists' => 'La divisa base seleccionada no existe.',
            'tax_cost.required' => 'El costo de impuestos es obligatorio.',
            'manufacturing_cost.required' => 'El costo de fabricacion es obligatorio.',
        ];
    }
}
