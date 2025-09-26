<?php

namespace App\Http\Controllers;

use App\Models\ProductStatus;
use Illuminate\Http\Request;

class ProductStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productStatuses = ProductStatus::all();
        return view('product-status.index', compact('productStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('product-status.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:product_statuses,code',
            'description' => 'nullable|string',
        ]);

        ProductStatus::create($request->only('code', 'description'));

        return redirect()->route('product-status.index')->with('success', 'Product status created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductStatus $productStatus)
    {
        return view('product-status.show', compact('productStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductStatus $productStatus)
    {
        return view('product-status.edit', compact('productStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductStatus $productStatus)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:product_statuses,code,' . $productStatus->id,
            'description' => 'nullable|string',
        ]);

        $productStatus->update($request->only('code', 'description'));

        return redirect()->route('product-status.index')->with('success', 'Product status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductStatus $productStatus)
    {
        $productStatus->delete();
        return redirect()->route('product-status.index')->with('success', 'Product status deleted successfully.');
    }
}
