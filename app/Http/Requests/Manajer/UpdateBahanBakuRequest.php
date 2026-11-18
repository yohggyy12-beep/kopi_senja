<?php

namespace App\Http\Requests\Manajer;

use App\Models\BahanBaku;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBahanBakuRequest extends FormRequest
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
        $bahanBaku = $this->route('bahanBaku');

        return [
            'nama' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'nama_normalisasi' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('bahan_baku', 'nama_normalisasi')
                    ->ignore($bahanBaku),
            ],

            'satuan_dasar' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['g', 'ml', 'pcs']),
            ],
            'stok_minimum' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],
            'kode_pemasok' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'nama_normalisasi.unique' =>
            'Nama bahan sudah digunakan bahan lain.',

            'satuan_dasar.in' =>
            'Satuan hanya boleh g, ml, atau pcs.',

            'stok_minimum.min' =>
            'Stok minimum tidak boleh negatif.',

        ];
    }
}
