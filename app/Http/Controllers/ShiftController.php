<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Machine;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('machine')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        $machines = Machine::all();
        return view('shifts.create', compact('machines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        Shift::create($request->all());
        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift)
    {
        $machines = Machine::all();
        return view('shifts.edit', compact('shift','machines'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        $shift->update($request->all());
        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }
}
