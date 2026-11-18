<?php

namespace App\Http\Requests\Manajer;

use App\Models\BahanBaku;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBahanBakuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->peran === User::PERAN_MANAGER;
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('nama')) {
            $nama = trim((string) $this->input('nama'));

            $this->merge([
                'nama' => $nama,
                'nama_normalisasi' => BahanBaku::nama_normalisasi($nama),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'nama_normalisasi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bahan_baku', 'nama_normalisasi'),
            ],
            'satuan_dasar' => [
                'required',
                'string',
                Rule::in(['g', 'ml', 'pcs']),
            ],
            'stok_minimum' => [
                'required',
                'numeric',
                'min:0',
            ],
            'kode_pemasok' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' =>
                'Nama bahan wajib diisi.',
            'nama.max' =>
                'Nama bahan maksimal 100 karakter.',
            'nama_normalisasi.unique' =>
                'Bahan dengan nama tersebut sudah tersedia.',
            'satuan_dasar.in' =>
                'Satuan dasar harus g, ml, atau pcs.',
            'stok_minimum.required' =>
                'Stok minimum wajib diisi.',
            'stok_minimum.numeric' =>
                'Stok minimum harus berupa angka.',
            'stok_minimum.min' =>
                'Stok minimum tidak boleh negatif.',
        ];
    }
}
