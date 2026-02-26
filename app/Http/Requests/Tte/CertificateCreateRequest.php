<?php

namespace App\Http\Requests\Tte;

use Illuminate\Foundation\Http\FormRequest;

class CertificateCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'certificate_no' => ['required','string','max:100','unique:certificates,certificate_no'],
            'title' => ['required','string','max:200'],
            'owner_name' => ['nullable','string','max:200'],
            'owner_identifier' => ['nullable','string','max:50'],
            'approval_level_required' => ['required','integer','min:1','max:10'],
        ];
    }
}