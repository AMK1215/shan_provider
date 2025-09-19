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
        // Check if the request has a 'req' wrapper
        $data = $this->all();
        
        if (isset($data['req'])) {
            // Handle 'req' wrapper format
            if (is_array($data['req']) && isset($data['req'][0]) && is_array($data['req'][0])) {
                // Array of objects format with req wrapper
                return [
                    'req' => 'required|array',
                    'req.*.roomId' => 'required|integer',
                    'req.*.matchId' => 'required|string|max:255',
                    'req.*.winNumber' => 'required|integer',
                    'req.*.players' => 'required|array',
                    'req.*.players.*.playerId' => 'required|string|max:255',
                    'req.*.players.*.betInfos' => 'required|array',
                    'req.*.players.*.winLoseAmount' => 'required|numeric',
                    'req.*.players.*.betInfos.*.betNumber' => 'required|integer',
                    'req.*.players.*.betInfos.*.betAmount' => 'required|numeric|min:0',
                ];
            } else {
                // Single object format with req wrapper
                return [
                    'req' => 'required|array',
                    'req.roomId' => 'required|integer',
                    'req.matchId' => 'required|string|max:255',
                    'req.winNumber' => 'required|integer',
                    'req.players' => 'required|array',
                    'req.players.*.playerId' => 'required|string|max:255',
                    'req.players.*.betInfos' => 'required|array',
                    'req.players.*.winLoseAmount' => 'required|numeric',
                    'req.players.*.betInfos.*.betNumber' => 'required|integer',
                    'req.players.*.betInfos.*.betAmount' => 'required|numeric|min:0',
                ];
            }
        } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
            // Array of objects format (direct)
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
            // Single object format (direct)
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
