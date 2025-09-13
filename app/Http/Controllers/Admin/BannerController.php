<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('admin_id', auth()->id())->get(); // Fetch banners for the logged-in admin
        // return $banners;

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $ext = $image->getClientOriginalExtension();
        $filename = uniqid('banner').'.'.$ext;
        $image->move(public_path('assets/img/banners/'), $filename);
        Banner::create([
            'image' => $filename,
            'admin_id' => auth()->id(),
        ]);

        return redirect(route('admin.banners.index'))->with('success', 'New Banner Image Added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        if (! $banner) {
            return redirect()->back()->with('error', 'Banner Not Found');
        }
        $image = $request->file('image');

        if ($image) {
            File::delete(public_path('assets/img/banners/'.$banner->image));
            $ext = $image->getClientOriginalExtension();
            $filename = uniqid('banner').'.'.$ext;
            $image->move(public_path('assets/img/banners/'), $filename);

            $banner->update([
                'image' => $filename,
            ]);
        }

        return redirect(route('admin.banners.index'))->with('success', 'Banner Image Updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if (! $banner) {
            return redirect()->back()->with('error', 'Banner Not Found');
        }

        File::delete(public_path('assets/img/banners/'.$banner->image));
        $banner->delete();

        return redirect()->back()->with('success', 'Banner Deleted.');
    }
}
