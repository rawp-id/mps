<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoController extends Controller
{
    public function index()
    {
        $cos = \App\Models\Co::all();
        return view('co.index', compact('cos'));
    }

    public function create()
    {
        return view('co.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        \App\Models\Co::create($request->all());

        return redirect()->route('co.index')->with('success', 'CO created successfully.');
    }

    public function show($id)
    {
        $co = \App\Models\Co::findOrFail($id);
        return view('co.show', compact('co'));
    }

    public function edit($id)
    {
        $co = \App\Models\Co::findOrFail($id);
        return view('co.edit', compact('co'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $co = \App\Models\Co::findOrFail($id);
        $co->update($request->all());

        return redirect()->route('co.index')->with('success', 'CO updated successfully.');
    }

    public function destroy($id)
    {
        $co = \App\Models\Co::findOrFail($id);
        $co->delete();

        return redirect()->route('co.index')->with('success', 'CO deleted successfully.');
    }
}
