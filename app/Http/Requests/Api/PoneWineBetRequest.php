<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PoneWineBetRequest extends FormRequest
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
            '*.roomId' => 'required',
            '*.matchId' => 'required',
            '*.winNumber' => 'required',
            '*.players' => 'required|array',
            '*.players.*.playerId' => 'required',
            '*.players.*.betInfos' => 'required|array',
            '*.players.*.winLoseAmount' => 'required',
            '*.players.*.betInfos.*.betNumber' => 'required',
            '*.players.*.betInfos.*.betAmount' => 'required',
        ];
    }
}
