<?php

namespace App\Http\Requests\Certification;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'credential_id' => 'string|max:255',
            'credential_url' => 'nullable|url',
            'certificationImage' => 'nullable|array|max:1',
            'certificationImage.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:min_width=200,min_height=200',
            'technology_ids' => 'array',
            'technology_ids.*' => 'integer|exists:technologies,id',
        ];
    }
}
