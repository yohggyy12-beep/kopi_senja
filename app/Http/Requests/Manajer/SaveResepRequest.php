<?php

namespace App\Http\Requests\Manajer;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SaveResepRequest extends FormRequest
{
    /**
     * Memastikan hanya Manajer yang boleh mengelola resep.
     */
    public function authorize(): bool
    {
        return $this->user()?->peran === User::PERAN_MANAGER;
    }

    /**
     * Menyesuaikan input sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        // Mendukung payload baik dengan key 'items' maupun 'resep'
        if ($this->has('resep') && !$this->has('items')) {
            $this->merge([
                'items' => $this->input('resep'),
            ]);
        }
    }

    /**
     * Aturan validasi penyimpanan resep.
     */
    public function rules(): array
    {
        $rules = [
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.bahan_baku_id' => [
                'required',
                'integer',
                'exists:bahan_baku,id',
                'distinct',
            ],
            'items.*.jumlah_per_porsi' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];

        // Jika menu_id tidak disediakan via route parameter, maka wajib ada di body request
        if (!$this->route('menu')) {
            $rules['menu_id'] = [
                'required',
                'integer',
                'exists:menus,id',
            ];
        }

        return $rules;
    }

    /**
     * Pesan kustom kesalahan validasi.
     */
    public function messages(): array
    {
        return [
            'menu_id.required' => 'Menu wajib dipilih.',
            'menu_id.exists' => 'Menu yang dipilih tidak ditemukan.',
            'items.required' => 'Daftar bahan baku resep wajib diisi.',
            'items.array' => 'Daftar bahan baku resep harus berupa array.',
            'items.min' => 'Resep minimal harus memiliki 1 bahan baku.',
            'items.*.bahan_baku_id.required' => 'Bahan baku wajib dipilih.',
            'items.*.bahan_baku_id.exists' => 'Bahan baku yang dipilih tidak ditemukan.',
            'items.*.bahan_baku_id.distinct' => 'Bahan baku tidak boleh duplikat pada satu resep.',
            'items.*.jumlah_per_porsi.required' => 'Jumlah takaran per porsi wajib diisi.',
            'items.*.jumlah_per_porsi.numeric' => 'Jumlah takaran harus berupa angka.',
            'items.*.jumlah_per_porsi.gt' => 'Jumlah takaran harus lebih dari nol.',
        ];
    }
}
