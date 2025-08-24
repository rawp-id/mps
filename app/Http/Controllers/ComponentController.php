<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $components = Component::all();
        return view('components.index', compact('components'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('components.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
        ]);

        $request->merge(['code' => 'C-' . rand(1000, 9999)]);

        Component::create($request->all());

        return redirect()->route('components.index')->with('success', 'Component created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Component $component)
    {
        return view('components.show', compact('component'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Component $component)
    {
        return view('components.edit', compact('component'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Component $component)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:100',
            'stock' => 'required|numeric|min:0',
        ]);

        $component->update($request->all());

        return redirect()->route('components.index')->with('success', 'Component updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Component $component)
    {
        $component->delete();

        return redirect()->route('components.index')->with('success', 'Component deleted successfully.');
    }
}
