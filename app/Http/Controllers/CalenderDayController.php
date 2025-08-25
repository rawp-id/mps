<?php

namespace App\Http\Controllers;

use App\Models\CalenderDay;
use Illuminate\Http\Request;

class CalenderDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calenderDays = CalenderDay::all();
        return view('calender-days.index', compact('calenderDays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('calender-days.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:calender_days,date',
            'is_workday' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        CalenderDay::create($request->all());

        return redirect()->route('calender-days.index')->with('success', 'Calender Day created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalenderDay $calenderDay)
    {
        return view('calender-days.show', compact('calenderDay'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalenderDay $calenderDay)
    {
        return view('calender-days.edit', compact('calenderDay'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalenderDay $calenderDay)
    {
        $request->validate([
            'date' => 'required|date|unique:calender_days,date,' . $calenderDay->id,
            'is_workday' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $calenderDay->update($request->all());

        return redirect()->route('calender-days.index')->with('success', 'Calender Day updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalenderDay $calenderDay)
    {
        $calenderDay->delete();
        return redirect()->route('calender-days.index')->with('success', 'Calender Day deleted successfully.');
    }
}
