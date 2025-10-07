<?php

namespace App\Http\Requests\WorkExperience;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkExperienceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'job_title'       => ['required','string','max:255'],
            'company_name'    => ['required','string','max:255'],
            'company_website' => ['nullable','url','max:255'],
            'description'     => ['required','string'],
            'start_date'      => ['required','date'],
            'end_date'        => ['required','date','after_or_equal:start_date'],
        ];
    }
}
