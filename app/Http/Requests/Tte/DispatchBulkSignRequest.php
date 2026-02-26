<?php

namespace App\Http\Requests\Tte;

use Illuminate\Foundation\Http\FormRequest;

class DispatchBulkSignRequest extends DispatchSignRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'certificate_ids'   => ['required', 'array', 'min:1', 'max:20'],
            'certificate_ids.*' => ['string'], // atau integer sesuai id sertifikat kamu
        ]);
    }
}