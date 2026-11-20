<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKasir() ?? false;
    }

    public function rules(): array
    {
        return [
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.menu_id' => [
                'required',
                'integer',
                'distinct',
                'exists:menus,id',
            ],

            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'metode_pembayaran' => [
                'required',
                'in:tunai,qris',
            ],

            'nomor_meja' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' =>
                'Pesanan harus memiliki minimal satu menu.',

            'items.*.menu_id.exists' =>
                'Menu yang dipilih tidak ditemukan.',

            'items.*.jumlah.min' =>
                'Jumlah pesanan minimal satu porsi.',

            'metode_pembayaran.in' =>
                'Metode pembayaran harus Tunai atau QRIS.',

            'nomor_meja.max' =>
                'Nomor meja maksimal 10.',
        ];
    }
}
