<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\Machine;
use App\Models\Operations;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function index()
    {
        $operations = Operations::with(['process', 'machine'])->get();
        // dd($operations); // Debugging line to check operations data
        return view('operations.index', compact('operations'));
    }

    public function create()
    {
        $processes = Process::all();
        $machines = Machine::all();
        return view('operations.create', compact('processes', 'machines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'process_id' => 'required|exists:processes,id',
            'machine_id' => 'required|exists:machines,id',
            'code' => 'nullable|unique:operations,code',
            'name' => 'nullable|string',
            'duration' => 'required|integer|min:0',
        ]);

        Operations::create($request->all());

        return redirect()->route('operations.index')->with('success', 'Operation created successfully.');
    }

    public function show(Operations $operation)
    {
        return view('operations.show', compact('operation'));
    }

    public function edit(Operations $operation)
    {
        $processes = Process::all();
        $machines = Machine::all();
        return view('operations.edit', compact('operation', 'processes', 'machines'));
    }

    public function update(Request $request, Operations $operation)
    {
        $request->validate([
            'process_id' => 'required|exists:processes,id',
            'machine_id' => 'required|exists:machines,id',
            'code' => 'nullable|unique:operations,code,' . $operation->id,
            'name' => 'nullable|string',
            'duration' => 'required|integer|min:0',
        ]);

        $operation->update($request->all());

        return redirect()->route('operations.index')->with('success', 'Operation updated successfully.');
    }

    public function destroy(Operations $operation)
    {
        $operation->delete();
        return redirect()->route('operations.index')->with('success', 'Operation deleted successfully.');
    }
}
