<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKasir() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'in:diproses,selesai',
            ],
        ];
    }
}
