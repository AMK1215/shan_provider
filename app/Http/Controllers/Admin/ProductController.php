<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $gameTypes = GameType::with(['products' => function ($query) {
            $query->withPivot('image');
        }])->where('status', 1)
            ->get();

        return view('admin.product.index', compact('gameTypes'));
    }

    public function toggleStatus(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->toggleStatus()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'newStatus' => $product->status,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update status.',
        ], 500);
    }

    public function GameTypeindex()
    {
        $gameTypes = GameType::all();

        return view('admin.product.game_typeindex', compact('gameTypes'));
    }

    public function GametoggleStatus(Request $request, $productId)
    {
        $product = GameType::findOrFail($productId);

        if ($product->GameTypetoggleStatus()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'newStatus' => $product->status,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update status.',
        ], 500);
    }

    public function edit($gameTypeId, $productId)
    {
        $gameType = GameType::with([
            'products' => function ($query) use ($productId) {
                $query->where('products.id', $productId);
            },
        ])->where('id', $gameTypeId)->first();

        return view('admin.product.edit', compact('gameType', 'productId'));
    }

    // public function update(Request $request, $gameTypeId, $productId)
    // {
    //     $image = $request->file('image');
    //     $ext = $image->getClientOriginalExtension();
    //     $filename = uniqid('game_type').'.'.$ext;
    //     $image->move(public_path('assets/img/game_logo/'), $filename);

    //     DB::table('game_type_product')->where('game_type_id', $gameTypeId)->where('product_id', $productId)
    //         ->update(['image' => $filename]);

    //     return redirect()->route('admin.gametypes.index');
    // }

    public function update(Request $request, $gameTypeId, $productId)
    {
        // Validate the file (optional, but good practice)
        $request->validate([
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $filename = uniqid('game_type').'.'.$ext;
            $image->move(public_path('assets/img/game_logo/'), $filename);

            // Optionally: delete old image (if you want)
            $oldImage = DB::table('game_type_product')
                ->where('game_type_id', $gameTypeId)
                ->where('product_id', $productId)
                ->value('image');
            if ($oldImage && file_exists(public_path('assets/img/game_logo/'.$oldImage))) {
                @unlink(public_path('assets/img/game_logo/'.$oldImage));
            }

            $data['image'] = $filename;
        }

        if (! empty($data)) {
            DB::table('game_type_product')
                ->where('game_type_id', $gameTypeId)
                ->where('product_id', $productId)
                ->update($data);
        }

        return redirect()->route('admin.gametypes.index')->with('success', 'Image updated!');
    }

    public function GameListFetch(Request $request)
    {
        $query = \App\Models\GameList::with('gameTypes');
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('provider', 'like', "%$search%")
                    ->orWhere('product_name', 'like', "%$search%")
                    ->orWhere('product_code', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('currency', 'like', "%$search%")
                    ->orWhere('product_title', 'like', "%$search%")
                    ->orWhere('game_type', 'like', "%$search%")
                    ->orWhere('short_name', 'like', "%$search%")
                    ->orWhere('provider_id', 'like', "%$search%")
                    ->orWhere('provider_product_id', 'like', "%$search%")
                    ->orWhere('order', 'like', "%$search%");
            });
        }
        $products = $query->orderBy('id', 'asc')->paginate(20);

        return view('admin.product.game_list', compact('products'));
    }

    // public function index(Request $request)
    // {
    //     $query = Product::with('gameTypes'); // eager load gameTypes for pivot image
    //     if ($search = $request->input('search')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('provider', 'like', "%$search%")
    //               ->orWhere('product_name', 'like', "%$search%")
    //               ->orWhere('product_code', 'like', "%$search%")
    //               ->orWhere('status', 'like', "%$search%")
    //               ->orWhere('currency', 'like', "%$search%")
    //               ->orWhere('product_title', 'like', "%$search%")
    //               ->orWhere('game_type', 'like', "%$search%")
    //               ->orWhere('short_name', 'like', "%$search%")
    //               ->orWhere('provider_id', 'like', "%$search%")
    //               ->orWhere('provider_product_id', 'like', "%$search%")
    //               ->orWhere('order', 'like', "%$search%")
    //               ;
    //         });
    //     }
    //     $products = $query->orderByDesc('id')->paginate(15);
    //     return view('admin.product.index', compact('products'));
    // }

    // public function create()
    // {
    //     $product = new Product();
    //     return view('admin.product.create', compact('product'));
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'provider' => 'required|string|max:255',
    //         'currency' => 'required|string|max:255',
    //         'status' => 'required|string|max:255',
    //         'provider_id' => 'required|integer',
    //         'provider_product_id' => 'required|integer',
    //         'product_code' => 'required|string|max:255',
    //         'product_name' => 'required|string|max:255',
    //         'game_type' => 'required|string|max:255',
    //         'product_title' => 'required|string|max:255',
    //         'short_name' => 'nullable|string|max:255',
    //         'order' => 'nullable|integer',
    //         'game_list_status' => 'nullable|boolean',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //     ]);

    //     $product = new Product($validated);
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $filename = Str::random(16) . '.' . $image->getClientOriginalExtension();
    //         $image->move(public_path('assets/img/game_logo/'), $filename);
    //         // Save image name in pivot if using many-to-many, else add a column to products table
    //         $product->image = $filename;
    //     }
    //     $product->save();
    //     return redirect()->route('admin.product.index')->with('success', 'Product created successfully!');
    // }

    // public function show(Product $product)
    // {
    //     return view('admin.product.show', compact('product'));
    // }

    // // public function edit(Product $product)
    // // {
    // //     return view('admin.product.edit', compact('product'));
    // // }
    // public function edit($gameTypeId, $productId)
    // {
    //     $gameType = GameType::with([
    //         'products' => function ($query) use ($productId) {
    //             $query->where('products.id', $productId);
    //         },
    //     ])->where('id', $gameTypeId)->first();

    //     return view('admin.product.edit', compact('gameType', 'productId'));
    // }

    // // public function update(Request $request, Product $product)
    // // {
    // //     $validated = $request->validate([
    // //         'provider' => 'required|string|max:255',
    // //         'currency' => 'required|string|max:255',
    // //         'status' => 'required|string|max:255',
    // //         'provider_id' => 'required|integer',
    // //         'provider_product_id' => 'required|integer',
    // //         'product_code' => 'required|string|max:255',
    // //         'product_name' => 'required|string|max:255',
    // //         'game_type' => 'required|string|max:255',
    // //         'product_title' => 'required|string|max:255',
    // //         'short_name' => 'nullable|string|max:255',
    // //         'order' => 'nullable|integer',
    // //         'game_list_status' => 'nullable|boolean',
    // //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    // //     ]);

    // //     if ($request->hasFile('image')) {
    // //         $image = $request->file('image');
    // //         $filename = Str::random(16) . '.' . $image->getClientOriginalExtension();
    // //         $image->move(public_path('assets/img/game_logo/'), $filename);
    // //         $product->image = $filename;
    // //     }
    // //     $product->update($validated);
    // //     $product->save();
    // //     return redirect()->route('admin.product.index')->with('success', 'Product updated successfully!');
    // // }

    // public function update(Request $request, $gameTypeId, $productId)
    // {
    //     $image = $request->file('image');
    //     $ext = $image->getClientOriginalExtension();
    //     $filename = uniqid('game_type').'.'.$ext;
    //     $image->move(public_path('assets/img/game_logo/'), $filename);

    //     DB::table('game_type_product')->where('game_type_id', $gameTypeId)->where('product_id', $productId)
    //         ->update(['image' => $filename]);

    //     return redirect()->route('admin.product.index')->with('success', 'Product updated successfully!');

    // }

    // public function destroy(Product $product)
    // {
    //     // Optionally delete image file
    //     if ($product->image && file_exists(public_path('assets/img/game_logo/' . $product->image))) {
    //         @unlink(public_path('assets/img/game_logo/' . $product->image));
    //     }
    //     $product->delete();
    //     return redirect()->route('admin.product.index')->with('success', 'Product deleted successfully!');
    // }
}
