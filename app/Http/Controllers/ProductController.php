<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:products,code',
            'name' => 'required',
            'shipping_date' => 'nullable|date',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'required|unique:products,code,' . $product->id,
            'name' => 'required',
            'shipping_date' => 'nullable|date',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function importForm()
    {
        return view('products.import');
    }

    // public function importPreview(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv,txt',
    //     ]);

    //     $path = $request->file('file')->store('temp');

    //     $data = Excel::toCollection(null, storage_path('app/' . $path))->first();

    //     if ($data->isEmpty()) {
    //         return back()->withErrors('File is empty or invalid format.');
    //     }

    //     // Remove header row if needed
    //     $rows = $data->skip(1)->values();

    //     // Simpan sementara di session untuk konfirmasi
    //     session(['import_products_data' => $rows]);

    //     return view('products.import-preview', [
    //         'headers' => $data->first(),
    //         'rows' => $rows,
    //     ]);
    // }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,txt',
        ]);

        // Parse langsung
        $collection = Excel::toCollection(null, $request->file('file'))->first();

        if ($collection->isEmpty()) {
            return back()->withErrors('File is empty or invalid format.');
        }

        // Misal header di baris pertama
        $headers = $collection->first();
        $rows = $collection->skip(1)->values();

        // SIMPAN array KE SESSION
        session([
            'import_products_headers' => $headers,
            'import_products_data' => $rows
        ]);

        return view('products.import-preview', compact('headers', 'rows'));
    }

    public function importStore(Request $request)
    {
        $rows = session('import_products_data');

        if (!$rows) {
            return redirect()->route('products.import')->withErrors('No data to import.');
        }

        foreach ($rows as $row) {
            Product::create([
                'code' => $row[0],
                'name' => $row[1],
                'shipping_date' => $row[2] ?? null,
            ]);
        }

        session()->forget(['import_products_data', 'import_products_headers']);

        return redirect()->route('products.index')->with('success', 'Products imported successfully.');
    }

}
