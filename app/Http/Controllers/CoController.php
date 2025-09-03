<?php

namespace App\Http\Controllers;

use App\Models\Co;
use App\Models\CoProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CoController extends Controller
{
    public function index()
    {
        $cos = \App\Models\Co::with('coProducts.product')->get();
        return view('co.index', compact('cos'));
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        return view('co.create', compact('products'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'co_products' => 'required|array',
            'co_products.*' => 'required|exists:products,id',
        ]);

        $request->merge([
            'code' => 'CO-' . strtoupper(Str::random(6))
        ]);

        $co = Co::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'code' => $request->input('code'),
        ]);

        foreach ($request->input('co_products') as $productId) {
            CoProduct::create([
                'co_id' => $co->id,
                'product_id' => $productId,
            ]);
        }

        return redirect()->route('co.index')->with('success', 'CO created successfully.');
    }

    public function show($id)
    {
        $co = \App\Models\Co::with('coProducts.product')->findOrFail($id);
        return view('co.show', compact('co'));
    }

    public function edit($id)
    {
        $co = \App\Models\Co::with('coProducts.product')->findOrFail($id);
        $products = \App\Models\Product::all();
        return view('co.edit', compact('co', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'co_products' => 'required|array',
            'co_products.*' => 'required|exists:products,id',
        ]);

        $co = \App\Models\Co::findOrFail($id);
        $co->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        foreach ($request->input('co_products') as $productId) {
            CoProduct::updateOrCreate(
                ['co_id' => $co->id, 'product_id' => $productId],
                ['co_id' => $co->id, 'product_id' => $productId]
            );
        }

        return redirect()->route('co.index')->with('success', 'CO updated successfully.');
    }

    public function destroy($id)
    {
        $co = \App\Models\Co::findOrFail($id);
        $co->delete();

        return redirect()->route('co.index')->with('success', 'CO deleted successfully.');
    }
}
