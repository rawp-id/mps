<?php

namespace App\Http\Controllers;

use App\Models\Downtime;
use App\Models\Machine;
use Illuminate\Http\Request;

class DowntimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $downtimes = Downtime::all();
        return view('downtimes.index', compact('downtimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::all();
        return view('downtimes.create', compact('machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'required|string|max:255',
        ]);

        Downtime::create($request->all());

        return redirect()->route('downtimes.index')->with('success', 'Downtime created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Downtime $downtime)
    {
        return view('downtimes.show', compact('downtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Downtime $downtime)
    {
        $machines = Machine::all();
        return view('downtimes.edit', compact('downtime', 'machines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Downtime $downtime)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'required|string|max:255',
        ]);

        $downtime->update($request->all());

        return redirect()->route('downtimes.index')->with('success', 'Downtime updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Downtime $downtime)
    {
        $downtime->delete();

        return redirect()->route('downtimes.index')->with('success', 'Downtime deleted successfully.');
    }
}
