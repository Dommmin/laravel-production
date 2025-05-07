<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexArticleRequest extends FormRequest
{
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
            'q' => ['string', 'nullable'],
            'tag' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'radius' => ['integer', 'nullable'],
            'lat' => ['numeric', 'nullable'],
            'lon' => ['numeric', 'nullable'],
            'page' => ['integer', 'min:1', 'nullable'],
            'size' => ['integer', 'min:1', 'max:100', 'nullable'],
        ];
    }
}
