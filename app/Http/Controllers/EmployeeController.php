<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with('employeeShifts.shift.operation')->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shifts = Shift::all();
        return view('employees.create', compact('shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:employees',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'shifts.*.shift_id' => 'required|exists:shifts,id',
            'shifts.*.role' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
            ]);

            foreach ($request->shifts as $shift) {
                EmployeeShift::create([
                    'employee_id' => $employee->id,
                    'shift_id' => $shift['shift_id'],
                    'role' => $shift['role'] ?? null,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('employeeShifts.shift');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $shifts = Shift::all();
        return view('employees.edit', compact('employee', 'shifts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'shifts.*.shift_id' => 'required|exists:shifts,id',
            'shifts.*.role' => 'nullable|string|max:255',
        ]);

        $employee->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
        ]);

        // Update shifts
        $employee->employeeShifts()->delete();
        foreach ($request->shifts as $shift) {
            EmployeeShift::create([
                'employee_id' => $employee->id,
                'shift_id' => $shift['shift_id'],
                'role' => $shift['role'] ?? null,
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
