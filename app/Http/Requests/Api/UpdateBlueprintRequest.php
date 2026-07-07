<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBlueprintRequest extends FormRequest
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
            'name'             => 'sometimes|string|max:255',
            'tone_description' => 'sometimes|nullable|string|max:1000',
            'max_characters'   => 'sometimes|nullable|integer|min:1|max:280',
            'max_hashtags'     => 'sometimes|nullable|integer|min:0|max:10',
            'extra_rules'      => 'sometimes|nullable|array',
            'extra_rules.*'    => 'string|max:255',
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
            'name.max'                => 'The blueprint name cannot exceed 255 characters.',
            'tone_description.max'    => 'The tone description cannot exceed 1000 characters.',
            'max_characters.integer'  => 'The max characters must be a number.',
            'max_characters.min'      => 'The max characters must be at least 1.',
            'max_characters.max'      => 'The max characters cannot exceed 280.',
            'max_hashtags.integer'    => 'The max hashtags must be a number.',
            'max_hashtags.min'        => 'The max hashtags cannot be negative.',
            'max_hashtags.max'        => 'The max hashtags cannot exceed 10.',
            'extra_rules.array'       => 'The extra rules must be a list.',
            'extra_rules.*.string'    => 'Each rule must be a text string.',
            'extra_rules.*.max'       => 'Each rule cannot exceed 255 characters.',
        ];
    }
}