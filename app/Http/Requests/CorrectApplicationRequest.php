<?php

namespace App\Http\Requests;

/** Koreksi oleh Admin: aturan sama seperti kirim pengajuan + catatan koreksi wajib. */
class CorrectApplicationRequest extends SaveApplicationRequest
{
    public function isSubmit(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return \Illuminate\Support\Arr::except(parent::rules(), ['action']) + [
            'correctionNote' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['correctionNote' => 'catatan koreksi'];
    }
}
