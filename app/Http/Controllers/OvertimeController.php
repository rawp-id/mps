<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Overtime;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $overtimes = Overtime::all();
        return view('overtimes.index', compact('overtimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::all();
        return view('overtimes.create', compact('machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
            'machine_id' => 'required|exists:machines,id',
        ]);

        Overtime::create($request->all());

        return redirect()->route('overtimes.index')->with('success', 'Overtime created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        $overtime->load('machine');
        return view('overtimes.show', compact('overtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        $machines = Machine::all();
        return view('overtimes.edit', compact('overtime', 'machines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Overtime $overtime)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'reason' => 'required|string|max:255',
            'machine_id' => 'required|exists:machines,id',
        ]);

        $overtime->update($request->all());

        return redirect()->route('overtimes.index')->with('success', 'Overtime updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        $overtime->delete();
        return redirect()->route('overtimes.index')->with('success', 'Overtime deleted successfully.');
    }
}
