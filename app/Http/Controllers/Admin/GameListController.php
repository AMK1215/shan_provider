<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class GameListController extends Controller
{
    // get game list with pagination 20
    public function GetGameList()
    {
        // $game_lists = GameList::paginate(20);
        $game_lists = GameList::orderBy('id', 'asc')->paginate(20);

        return view('admin.game_list.paginate_index', compact('game_lists'));
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = GameList::with(['gameType', 'product']);

    //         return Datatables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('order', function ($row) {
    //                 return $row->order;
    //             })
    //             ->addColumn('game_type', function ($row) {
    //                 return $row->gameType->name ?? 'N/A';
    //             })
    //             ->addColumn('product', function ($row) {
    //                 return $row->product->name ?? 'N/A';
    //             })
    //             ->addColumn('game_name', function ($row) {
    //                 return $row->name ?? 'N/A';
    //             })
    //             ->addColumn('status', function ($row) {
    //                 return $row->status == 1 ? 'Running Game' : 'Game is Closed';
    //             })
    //             // ->addColumn('pp_hot', function ($row) {
    //             //     return $row->pp_hot == 1 ? 'PP Hot' : '--';
    //             // })
    //             ->addColumn('hot_status', function ($row) {
    //                 return $row->hot_status == 1 ? 'This Game is Hot' : 'Game is Normal';
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $btn = '<form action="'.route('admin.gameLists.toggleStatus', $row->id).'" method="POST" style="display:inline;">
    //                             '.csrf_field().'
    //                             '.method_field('PATCH').'
    //                             <button type="submit" class="btn btn-warning btn-sm">GameStatus</button>
    //                         </form>';
    //                 $btn .= '<form action="'.route('admin.HotGame.toggleStatus', $row->id).'" method="POST" style="display:inline;">
    //                             '.csrf_field().'
    //                             '.method_field('PATCH').'
    //                             <button type="submit" class="btn btn-success btn-sm">HotGame</button>
    //                         </form>';

    //                 $btn .= '<form action="'.route('admin.PPHotGame.toggleStatus', $row->id).'" method="POST" style="display:inline;">
    //                             '.csrf_field().'
    //                             '.method_field('PATCH').'
    //                             <button type="submit" class="btn btn-warning btn-sm">PPHot</button>
    //                         </form>';

    //                 $btn .= '<a href="'.route('admin.game_list.edit', $row->id).'" class="btn btn-primary btn-sm">EditImage</a>';
    //                 $btn .= '<a href="'.route('admin.game_list_order.edit', $row->id).'" class="btn btn-primary btn-sm">Order</a>';

    //                 return $btn;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('admin.game_list.paginate_index');
    // }

    public function toggleStatus($id)
    {
        $game = GameList::findOrFail($id);
        $game->status = $game->status == 1 ? 0 : 1;
        $game->save();

        return redirect()->route('admin.gameLists.index')->with('success', 'Game status updated successfully.');
    }

    public function HotGameStatus($id)
    {
        $game = GameList::findOrFail($id);
        $game->hot_status = $game->hot_status == 1 ? 0 : 1;
        $game->save();

        return redirect()->route('admin.gameLists.index')->with('success', 'HotGame status updated successfully.');
    }

    public function PPHotGameStatus($id)
    {
        $game = GameList::findOrFail($id);
        $game->pp_hot = $game->pp_hot == 1 ? 0 : 1;
        $game->save();

        return redirect()->route('admin.gameLists.index')->with('success', 'PP HotGame status updated successfully.');
    }

    public function GameListOrderedit(GameList $gameList)
    {
        return view('admin.game_list.order_edit', compact('gameList'));
    }

    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'order' => 'required|integer|min:0',
        ]);

        $gameList = GameList::findOrFail($id);

        $gameList->order = $request->input('order');
        $gameList->save();

        return redirect()->route('admin.gameLists.index')->with('success', 'Game list order  updated successfully.');
    }

    public function updateAllOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|integer',
        ]);

        $newOrderValue = $request->input('order');

        $updatedCount = GameList::query()->update(['order' => $newOrderValue]);

        return redirect()
            ->back()
            ->with('success', "Order column updated for all rows successfully. Updated rows: $updatedCount.");
    }

    /**
     * Update the image_url for a specific game.
     */
    public function edit(GameList $gameList)
    {
        return view('admin.game_list.edit', compact('gameList'));
    }

    public function updateImageUrl(Request $request, $id)
    {
        $game = GameList::findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');

        if ($image && $image->isValid()) {
            $filename = $image->getClientOriginalName();

            $image->move(public_path('assets/img/game_list/'), $filename);

            $game->update([
                'image_url' => 'https://ponewine20x.xyz/assets/img/game_list/'.$filename,
            ]);

            return redirect()->route('admin.gameLists.index')->with('success', 'Image updated successfully.');
        }

        return redirect()->back()->withErrors('File upload failed.');
    }
}
