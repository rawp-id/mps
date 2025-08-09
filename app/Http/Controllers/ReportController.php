<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $products = Product::where('name', 'like', '%' . request('search') . '%')
            ->whereHas('schedules', function ($query) {
                $query->where('is_completed', false);
            })
            ->paginate(10);
        // dd($products->first());
        return view('reports.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $schedules = Schedule::with(['product', 'process', 'machine', 'operation', 'operation.process', 'operation.machine'])
            ->where('product_id', $id)
            ->orderBy('start_time')
            ->get();
        $machines = Machine::all();
        // dd($schedules);
        return view('reports.show', compact('schedules', 'product', 'machines'));
    }

    public function updateProcessStatus(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['status' => $request->input('status')]);
        return redirect()->route('reports.index')->with('success', 'Process status updated successfully.');
    }

    public function updateIsCompleted(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['is_completed' => $request->input('is_completed')]);
        return redirect()->route('reports.index')->with('success', 'Schedule completion status updated successfully.');
    }
}
