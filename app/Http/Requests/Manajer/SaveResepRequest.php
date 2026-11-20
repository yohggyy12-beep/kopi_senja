<?php

namespace App\Http\Requests\Manajer;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveResepRequest extends FormRequest
{
    /**
     * Hanya Manajer yang boleh mengelola resep.
     */
    public function authorize(): bool
    {
        return $this->user()?->peran === User::PERAN_MANAGER;
    }

    /**
     * Aturan validasi komposisi resep.
     */
    public function rules(): array
    {
        return [

            // Resep harus berupa array.
            'bahan' => [
                'required',
                'array',
                'min:1',
            ],

            // Setiap bahan wajib mempunyai ID.
            'bahan.*.bahan_baku_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('bahan_baku', 'id'),
            ],

            // Takaran wajib berupa angka positif.
            'bahan.*.jumlah_per_porsi' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],

        ];
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [

            'bahan.required' =>
                'Komposisi resep wajib diisi.',

            'bahan.array' =>
                'Format komposisi resep tidak valid.',

            'bahan.min' =>
                'Resep minimal harus mempunyai satu bahan.',

            'bahan.*.bahan_baku_id.required' =>
                'Bahan baku wajib dipilih.',

            'bahan.*.bahan_baku_id.exists' =>
                'Bahan baku yang dipilih tidak tersedia.',

            'bahan.*.bahan_baku_id.distinct' =>
                'Bahan yang sama tidak boleh dimasukkan dua kali.',

            'bahan.*.jumlah_per_porsi.required' =>
                'Takaran setiap bahan wajib diisi.',

            'bahan.*.jumlah_per_porsi.numeric' =>
                'Takaran harus berupa angka.',

            'bahan.*.jumlah_per_porsi.gt' =>
                'Takaran harus lebih dari nol.',

            'bahan.*.jumlah_per_porsi.decimal' =>
                'Takaran maksimal memiliki tiga angka desimal.',

        ];
    }
}
