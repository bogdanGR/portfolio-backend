<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_title' => 'required|string|max:255',
            'short_bio' => 'required|string|max:500',
            'long_description' => 'required|string',
            'professional_summary' => 'string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:min_width=200,min_height=200',
            'resume' => 'nullable|file|mimes:pdf|max:5120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'github_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'years_experience' => 'nullable|string|max:50',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:100',
            'university' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'start_date_uni' => 'nullable|date',
            'end_date_uni' => 'nullable|date',
            'degree_url' => 'nullable|url|max:255',
            'diploma_thesis_url' => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'job_title.required' => 'Please enter your job title.',
            'job_title.string' => 'Job title must be a valid text.',
            'job_title.max' => 'Job title cannot exceed 255 characters.',
            'short_bio.required' => 'A short bio is required.',
            'short_bio.string' => 'Short bio must be a valid text.',
            'short_bio.max' => 'Short bio must not exceed 500 characters.',
            'long_description.required' => 'Please provide your full description.',
            'long_description.string' => 'Long description must be valid text.',
            'professional_summary.string' => 'Professional summary must be a valid text.',
            'avatar.image' => 'Avatar must be an image file (JPEG, PNG, JPG, or WEBP).',
            'avatar.mimes' => 'Avatar must be in JPEG, PNG, JPG, or WEBP format.',
            'avatar.max' => 'Avatar size must not exceed 2MB.',
            'avatar.dimensions' => 'Avatar image must be at least 200x200 pixels.',
            'resume.file' => 'Resume must be a valid file.',
            'resume.mimes' => 'Resume must be a PDF file.',
            'resume.max' => 'Resume size must not exceed 5MB.',
            'email.required' => 'Your email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'phone.string' => 'Phone number must be valid text.',
            'phone.max' => 'Phone number cannot exceed 50 characters.',
            'location.string' => 'Location must be valid text.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'github_url.url' => 'GitHub URL must be valid.',
            'github_url.max' => 'GitHub URL cannot exceed 255 characters.',
            'linkedin_url.url' => 'LinkedIn URL must be valid.',
            'linkedin_url.max' => 'LinkedIn URL cannot exceed 255 characters.',
            'website_url.url' => 'Website URL must be valid.',
            'website_url.max' => 'Website URL cannot exceed 255 characters.',
            'degree_url.url' => 'Degree link must be a valid URL.',
            'degree_url.max' => 'Degree link cannot exceed 255 characters.',
            'diploma_thesis_url.url' => 'Diploma thesis link must be a valid URL.',
            'diploma_thesis_url.max' => 'Diploma thesis link cannot exceed 255 characters.',
            'years_experience.string' => 'Years of experience must be valid text.',
            'years_experience.max' => 'Years of experience cannot exceed 50 characters.',
            'languages.array' => 'Languages must be provided as an array.',
            'languages.*.string' => 'Each language must be valid text.',
            'languages.*.max' => 'Each language cannot exceed 100 characters.',
            'university.string' => 'University name must be valid text.',
            'university.max' => 'University name cannot exceed 255 characters.',
            'degree.string' => 'Degree must be valid text.',
            'degree.max' => 'Degree cannot exceed 255 characters.',
            'start_date_uni.date' => 'Start date must be a valid date.',
            'end_date_uni.date' => 'End date must be a valid date.',
        ];
    }
}
