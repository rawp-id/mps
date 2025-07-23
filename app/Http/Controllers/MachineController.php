<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::all();
        return view('machines.index', compact('machines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        Machine::create($request->all());

        return redirect()->route('machines.index')->with('success', 'Machine created successfully.');
    }

    public function show(Machine $machine)
    {
        return view('machines.show', compact('machine'));
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        $machine->update($request->all());

        return redirect()->route('machines.index')->with('success', 'Machine updated successfully.');
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();
        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }
}
