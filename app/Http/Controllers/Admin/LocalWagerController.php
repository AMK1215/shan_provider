<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wager;
use Illuminate\Http\Request;

class LocalWagerController extends Controller
{
    public function index(Request $request)
    {
        $query = Wager::query();
        if ($request->filled('member_account')) {
            $query->where('member_account', $request->member_account);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->where('created_at_api', '>=', strtotime($request->start_date) * 1000);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at_api', '<=', strtotime($request->end_date) * 1000);
        }
        $wagers = $query->orderByDesc('created_at_api')->paginate(50);

        return view('admin.local_wager.index', compact('wagers'));
    }

    public function show($id)
    {
        $wager = Wager::findOrFail($id);

        return view('admin.local_wager.show', compact('wager'));
    }
}
