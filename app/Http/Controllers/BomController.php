<?php

namespace App\Http\Controllers;

use App\Models\BOM;
use App\Models\Component;
use App\Models\Product;
use Illuminate\Http\Request;

class BomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boms = BOM::all();
        return view('boms.index', compact('boms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        $components = Component::all();
        return view('boms.create', compact('products', 'components'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'component_id' => 'required|exists:components,id',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'usage_type' => 'required|in:consumable,usage_based',
        ]);

        BOM::create($request->all());

        return redirect()->route('boms.index')->with('success', 'BOM created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BOM $bom)
    {
        $bom->load('product', 'component');
        return view('boms.show', compact('bom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BOM $bom)
    {
        $products = Product::all();
        $components = Component::all();
        return view('boms.edit', compact('bom', 'products', 'components'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BOM $bom)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'component_id' => 'required|exists:components,id',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'usage_type' => 'required|in:consumable,usage_based',
        ]);

        $bom->update($request->all());

        return redirect()->route('boms.index')->with('success', 'BOM updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BOM $bom)
    {
        $bom->delete();
        return redirect()->route('boms.index')->with('success', 'BOM deleted successfully.');
    }
}
