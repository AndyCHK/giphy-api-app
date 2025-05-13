<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;

class SaveFavoriteRequest extends FormRequest
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
            'gif_id' => 'required|string',
            'alias' => 'required|string|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'gif_id.required' => 'El ID del GIF es obligatorio',
            'gif_id.string' => 'El ID del GIF debe ser una cadena de texto',
            'alias.required' => 'El alias es obligatorio',
            'alias.string' => 'El alias debe ser una cadena de texto',
            'alias.max' => 'El alias no debe exceder los 255 caracteres',
        ];
    }
} 