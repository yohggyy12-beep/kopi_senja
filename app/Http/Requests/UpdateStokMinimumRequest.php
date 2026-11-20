<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStokMinimumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'stok_minimum' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,3',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'stok_minimum.required' =>
                'Batas minimum stok wajib diisi.',

            'stok_minimum.numeric' =>
                'Batas minimum stok harus berupa angka.',

            'stok_minimum.min' =>
                'Batas minimum stok tidak boleh kurang dari nol.',

            'stok_minimum.decimal' =>
                'Batas minimum maksimal menggunakan tiga angka desimal.',
        ];
    }
}
