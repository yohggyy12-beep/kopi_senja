<?php

namespace App\Http\Requests\Manajer;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    /**
     * Memastikan hanya Manajer yang boleh memperbarui menu.
     */
    public function authorize(): bool
    {
        return $this->user()?->peran === User::PERAN_MANAGER;
    }

    /**
     * Membersihkan input.
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
     * Validasi perubahan menu.
     */
    public function rules(): array
    {
        return [

            'nama' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'kategori' => [
                'sometimes',
                'required',
                Rule::in([
                    'Minuman',
                    'Makanan',
                ]),
            ],

            'harga_jual' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

            'status_aktif' => [
                'sometimes',
                'required',
                'boolean',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'nama.required' =>
                'Nama menu wajib diisi.',

            'kategori.in' =>
                'Kategori hanya boleh Minuman atau Makanan.',

            'harga_jual.gt' =>
                'Harga jual harus lebih dari nol.',

            'status_aktif.boolean' =>
                'Status menu harus bernilai aktif atau nonaktif.',

        ];
    }
}
