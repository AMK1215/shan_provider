<?php

namespace App\Http\Requests\TwoD;

use Illuminate\Foundation\Http\FormRequest;

class TwoDPlayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Set to true if authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'totalAmount' => 'required|numeric|min:1',
            'amounts' => 'required|array',
            'amounts.*.num' => 'required|string', // Ensure 'num' is treated as string for '00' to '09'
            'amounts.*.amount' => 'required|integer|min:1',
        ];
    }
}
