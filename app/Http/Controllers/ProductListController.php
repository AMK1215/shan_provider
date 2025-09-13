<?php

namespace App\Http\Controllers;

use App\Services\ProductListService;
use Illuminate\Http\Request;

class ProductListController extends Controller
{
    public function index(Request $request)
    {
        $offset = $request->query('offset', 0);
        $size = $request->query('size');
        $result = ProductListService::getProductList((int) $offset, $size !== null ? (int) $size : null);

        return response()->json($result);
    }
}
