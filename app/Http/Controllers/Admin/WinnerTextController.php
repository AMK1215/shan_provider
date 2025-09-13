<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WinnerText;
use Illuminate\Http\Request;

class WinnerTextController extends Controller
{
    public function index()
    {
        $texts = WinnerText::where('owner_id', auth()->id())->get(); // Fetch banners for the logged-in admin

        return view('admin.winner_text.index', compact('texts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.winner_text.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required',
        ]);
        WinnerText::create([
            'text' => $request->text,
            'owner_id' => auth()->id(),

        ]);

        return redirect(route('admin.winner_text.index'))->with('success', 'New Text Created Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WinnerText $winnerText)
    {
        return view('admin.winner_text.show', compact('winnerText'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WinnerText $winnerText)
    {
        return view('admin.winner_text.edit', compact('winnerText'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WinnerText $winnerText)
    {
        $request->validate([
            'text' => 'required',
        ]);
        $winnerText->update([
            'text' => $request->text,
        ]);

        return redirect(route('admin.winner_text.index'))->with('success', 'Marquee Text Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WinnerText $winnerText)
    {
        $winnerText->delete();

        return redirect()->back()->with('success', 'Marquee Text Deleted Successfully.');
    }
}
