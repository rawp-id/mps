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
            'shipment_dates' => 'required|array',
            'shipment_dates.*' => 'required|date',
        ]);

        $request->merge([
            'code' => 'CO-' . strtoupper(Str::random(6)),
        ]);

        $co = Co::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'code' => $request->input('code'),
        ]);

        foreach ($request->input('co_products') as $index => $productId) {
            $shipmentDate = $request->input('shipment_dates')[$index];

            CoProduct::create([
                'co_id' => $co->id,
                'product_id' => $productId,
                'shipment_date' => $shipmentDate,
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
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'co_products' => 'required|array',
            'co_products.*' => 'required|exists:products,id',
            'shipment_dates' => 'required|array',
            'shipment_dates.*' => 'required|date',
        ]);

        // Find the CO to update
        $co = \App\Models\Co::findOrFail($id);

        // Update the main CO details
        $co->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        // Detach any products that were removed (i.e., not present in the current form)
        $currentProductIds = $request->input('co_products');
        CoProduct::where('co_id', $co->id)
            ->whereNotIn('product_id', $currentProductIds)
            ->delete();

        // Loop through the selected products and their associated shipment dates
        foreach ($currentProductIds as $index => $productId) {
            // Get the shipment date for the current product
            $shipmentDate = $request->input('shipment_dates')[$index];

            // Update or create the CoProduct relationship with the shipment date
            \App\Models\CoProduct::updateOrCreate(
                ['co_id' => $co->id, 'product_id' => $productId],
                ['shipment_date' => $shipmentDate]  // Update the shipment date
            );
        }

        // Redirect back with a success message
        return redirect()->route('co.index')->with('success', 'CO updated successfully.');
    }


    public function destroy($id)
    {
        $co = \App\Models\Co::findOrFail($id);
        $co->delete();

        return redirect()->route('co.index')->with('success', 'CO deleted successfully.');
    }
}
