<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::all();
        return view('processes.index', compact('processes'));
    }

    public function create()
    {
        return view('processes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:processes,code',
            'name' => 'required|string',
            'speed' => 'required|integer|min:0',
        ]);

        Process::create($request->all());

        return redirect()->route('processes.index')->with('success', 'Process created successfully.');
    }

    public function show(Process $process)
    {
        return view('processes.show', compact('process'));
    }

    public function edit(Process $process)
    {
        return view('processes.edit', compact('process'));
    }

    public function update(Request $request, Process $process)
    {
        $request->validate([
            'code' => 'required|string|unique:processes,code,' . $process->id,
            'name' => 'required|string',
            'speed' => 'required|integer|min:0',
        ]);

        $process->update($request->all());

        return redirect()->route('processes.index')->with('success', 'Process updated successfully.');
    }

    public function destroy(Process $process)
    {
        $process->delete();
        return redirect()->route('processes.index')->with('success', 'Process deleted successfully.');
    }
}
