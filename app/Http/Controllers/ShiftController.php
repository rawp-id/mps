<?php

namespace App\Http\Controllers;

use App\Models\Operations;
use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('operation')->get();
        
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        $operations = Operations::all();
        return view('shifts.create', compact('operations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'name' => 'required|string|max:255',
            'day' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        Shift::create($request->all());
        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift)
    {
        $operations = Operations::all();
        return view('shifts.edit', compact('shift','operations'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'name' => 'required|string|max:255',
            'day' => 'required|date',
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
