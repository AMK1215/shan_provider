<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentRequest extends FormRequest
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
            'user_name' => ['required', 'string', 'unique:users,user_name'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'unique:users,phone'],
            'password' => 'required|min:6',
            'amount' => 'nullable|numeric',
            'referral_code' => ['required', 'unique:users,referral_code'],
            'shan_agent_code' => ['required', 'string', 'unique:users,shan_agent_code'],
            'shan_agent_name' => ['required', 'string'],
            'shan_secret_key' => ['required', 'string'],
            'shan_callback_url' => ['required', 'string'],
        ];
    }
}
