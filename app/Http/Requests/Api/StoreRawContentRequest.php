<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRawContentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blueprint_id' => 'required|integer|exists:campaign_blueprints,id',
            'content'      => 'required|string|min:10|max:10000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'blueprint_id.required' => 'A campaign blueprint is required.',
            'blueprint_id.exists'   => 'The selected blueprint does not exist.',
            'content.required'      => 'The raw content is required.',
            'content.min'           => 'The content must be at least 10 characters.',
            'content.max'           => 'The content cannot exceed 10000 characters.',
        ];
    }
}