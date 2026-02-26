<?php

namespace App\Http\Requests\Tte;

use Illuminate\Foundation\Http\FormRequest;

class DispatchSignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'signer_certificate_id' => ['required', 'integer', 'exists:signer_certificates,id'],

            // visibility
            'barcode_visible' => ['nullable', 'boolean'],
            'tte_visible'     => ['nullable', 'boolean'],

            // appearance (opsional)
            'appearance_page' => ['nullable', 'integer', 'min:1', 'max:10'],
            'appearance_x'    => ['nullable', 'integer', 'min:0', 'max:10000'],
            'appearance_y'    => ['nullable', 'integer', 'min:0', 'max:10000'],
            'appearance_w'    => ['nullable', 'integer', 'min:1', 'max:10000'],
            'appearance_h'    => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'signer_certificate_id.required' => 'Signer wajib dipilih.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Checkbox HTML: jika tidak dicentang, field tidak terkirim.
        // Kita normalisasi jadi boolean (0/1)
        $this->merge([
            'barcode_visible' => $this->boolean('barcode_visible'),
            'tte_visible'     => $this->boolean('tte_visible'),
        ]);
    }
}