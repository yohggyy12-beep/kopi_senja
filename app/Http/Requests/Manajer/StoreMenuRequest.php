<?php

namespace App\Http\Requests\Manajer;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    /**
     * Memastikan hanya Manajer yang boleh membuat menu.
     */
    public function authorize(): bool
    {
        return $this->user()?->peran === User::PERAN_MANAGER;
    }

    /**
     * Membersihkan input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->exists('nama')) {
            $this->merge([
                'nama' => trim((string) $this->input('nama')),
            ]);
        }

        if ($this->exists('kategori')) {
            $this->merge([
                'kategori' => trim(
                    (string) $this->input('kategori')
                ),
            ]);
        }
    }

    /**
     * Aturan validasi pembuatan menu.
     */
    public function rules(): array
    {
        return [

            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'kategori' => [
                'required',
                Rule::in([
                    'Minuman',
                    'Makanan',
                ]),
            ],

            'harga_jual' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'nama.required' =>
                'Nama menu wajib diisi.',

            'nama.max' =>
                'Nama menu maksimal 100 karakter.',

            'kategori.required' =>
                'Kategori menu wajib dipilih.',

            'kategori.in' =>
                'Kategori hanya boleh Minuman atau Makanan.',

            'harga_jual.required' =>
                'Harga jual wajib diisi.',

            'harga_jual.numeric' =>
                'Harga jual harus berupa angka.',

            'harga_jual.gt' =>
                'Harga jual harus lebih dari nol.',

            'harga_jual.decimal' =>
                'Harga jual maksimal memiliki dua angka desimal.',

        ];
    }
}
