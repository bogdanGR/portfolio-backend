<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'long_description' => 'required|string',
            'link' => 'nullable|url',
            'github' => 'nullable|url',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'technology_ids' => 'array',
            'technology_ids.*' => 'integer|exists:technologies,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'short_description.required' => 'Short description is required.',
            'long_description.required' => 'Long description is required.',
            'link.url' => 'The project link must be a valid URL.',
            'github.url' => 'The GitHub link must be a valid URL.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
