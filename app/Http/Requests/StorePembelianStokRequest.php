<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembelianStokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'bahan_baku_id' => [
                'required',
                'integer',
                'exists:bahan_baku,id',
            ],

            'jumlah' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],

            'tanggal_pembelian' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bahan_baku_id.required' =>
                'Bahan baku wajib dipilih.',

            'bahan_baku_id.exists' =>
                'Bahan baku tidak ditemukan.',

            'jumlah.required' =>
                'Jumlah pembelian wajib diisi.',

            'jumlah.numeric' =>
                'Jumlah pembelian harus berupa angka.',

            'jumlah.gt' =>
                'Jumlah pembelian harus lebih dari nol.',

            'jumlah.decimal' =>
                'Jumlah maksimal menggunakan tiga angka desimal.',

            'tanggal_pembelian.required' =>
                'Tanggal pembelian wajib diisi.',

            'tanggal_pembelian.date' =>
                'Format tanggal pembelian tidak valid.',
        ];
    }
}
