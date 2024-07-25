<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{

//    public function attributes():array
//    {
//        return [
//          'name' => 'Maxsulot nomi majburiy!',
//          'description' => 'Maxsulot uchun izox yozing!',
//            'price' => 'Maxsulot narxini belgilang!',
//            'category_id' => 'Maxsulot kategoriyasini tanlang',
//        ];
//    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
