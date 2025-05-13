<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Giphy;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'query' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
            'offset' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'query.required' => 'El término de búsqueda es obligatorio',
            'query.min' => 'El término de búsqueda debe tener al menos 1 caracter',
            'query.max' => 'El término de búsqueda no debe exceder los 100 caracteres',
            'limit.integer' => 'El límite debe ser un número entero',
            'limit.min' => 'El límite mínimo es 1',
            'limit.max' => 'El límite máximo es 50',
            'offset.integer' => 'El desplazamiento debe ser un número entero',
            'offset.min' => 'El desplazamiento mínimo es 0',
        ];
    }
} 