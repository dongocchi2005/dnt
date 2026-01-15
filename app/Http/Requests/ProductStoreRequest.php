<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $options = array_filter((array)$this->input('options', []), function ($option) {
            $values = trim((string)($option['values'] ?? ''));
            return $values !== '';
        });

        $this->merge([
            'options' => array_values($options),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','unique:products,slug'],
            'description' => ['nullable','string'],
            'category_id' => ['nullable','exists:categories,id'],
            'original_price' => ['required','numeric'],
            'sale_price' => ['required','numeric'],
            'image' => ['nullable','image','max:2048'],
            'stock' => ['nullable','integer'],
            'size' => ['nullable','string','max:255'],
            'color' => ['nullable','string','max:255'],
            'is_active' => ['nullable','boolean'],
            'is_clearance' => ['nullable','boolean'],

            'options' => ['array'],
            'options.*.name' => ['required','string','max:255'],
            'options.*.values' => ['required','string'], // comma-separated

            'variants' => ['array'],
            'variants.*.combo' => ['required','string','max:1000'], // "Màu:Đen|Size:M"
            'variants.*.sku' => ['nullable','string','max:255','distinct'],
            'variants.*.price' => ['required','numeric'],
            // no stock
            'variants.*.is_default' => ['nullable','boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($v){
            $variants = (array)$this->input('variants', []);
            if (!empty($this->input('options')) && empty($variants)) {
                $v->errors()->add('variants', 'Bạn phải tạo tổ hợp biến thể khi đã nhập phân loại.');
            }
            $defaults = collect($variants)->filter(fn($x) => !empty($x['is_default']))->count();
            if ($defaults > 1) {
                $v->errors()->add('variants', 'Chỉ một biến thể được chọn làm mặc định.');
            }
            if (count($variants) > 100) {
                $v->errors()->add('variants', 'Số lượng biến thể vượt quá giới hạn 100.');
            }
            foreach ($variants as $i => $row) {
                if (isset($row['price']) && !is_numeric($row['price'])) {
                    $v->errors()->add("variants.$i.price", 'Giá không hợp lệ.');
                }
            }
        });
    }
}
