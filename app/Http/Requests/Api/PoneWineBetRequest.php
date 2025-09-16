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
        // Check if the request is an array of objects or a single object
        $data = $this->all();
        
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            // Array of objects format
            return [
                '*.roomId' => 'required|integer',
                '*.matchId' => 'required|string|max:255',
                '*.winNumber' => 'required|integer',
                '*.players' => 'required|array',
                '*.players.*.playerId' => 'required|string|max:255',
                '*.players.*.betInfos' => 'required|array',
                '*.players.*.winLoseAmount' => 'required|numeric',
                '*.players.*.betInfos.*.betNumber' => 'required|integer',
                '*.players.*.betInfos.*.betAmount' => 'required|numeric|min:0',
            ];
        } else {
            // Single object format
            return [
                'roomId' => 'required|integer',
                'matchId' => 'required|string|max:255',
                'winNumber' => 'required|integer',
                'players' => 'required|array',
                'players.*.playerId' => 'required|string|max:255',
                'players.*.betInfos' => 'required|array',
                'players.*.winLoseAmount' => 'required|numeric',
                'players.*.betInfos.*.betNumber' => 'required|integer',
                'players.*.betInfos.*.betAmount' => 'required|numeric|min:0',
            ];
        }
    }
}
