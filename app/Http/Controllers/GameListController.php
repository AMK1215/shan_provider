<?php

namespace App\Http\Controllers;

use App\Services\GameListService;
use Illuminate\Http\Request;

class GameListController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'product_code' => 'required|integer',
            'operator_code' => 'required|string',
            'game_type' => 'string|nullable',
        ]);

        $product_code = $request->input('product_code');
        $operator_code = $request->input('operator_code');
        $game_type = $request->input('game_type');
        $offset = $request->has('offset') ? (int) $request->input('offset') : 0;
        $size = $request->has('size') ? (int) $request->input('size') : null;

        $result = GameListService::getGameList($product_code, $operator_code, $game_type, $offset, $size);

        return response()->json($result);
    }
}
