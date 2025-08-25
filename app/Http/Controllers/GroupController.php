<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupingProccess;
use App\Models\GroupingProcess;
use App\Models\ProcessProduct;

class GroupController extends Controller
{
    // List semua group
    public function index()
    {
        $groups = Group::with('groupingProcesses.processProduct')->get();
        return view('groups.index', compact('groups'));
    }

    // Form create group
    public function create()
    {
        $processProducts = ProcessProduct::all();
        return view('groups.create', compact('processProducts'));
    }

    // Simpan group + prosesnya
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'process_products' => 'array',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if($request->has('process_products')) {
            foreach($request->process_products as $pp_id){
                GroupingProccess::create([
                    'group_id' => $group->id,
                    'process_product_id' => $pp_id
                ]);
            }
        }

        return redirect()->route('groups.index')->with('success','Group created.');
    }

    // Form edit
    public function edit(Group $group)
    {
        $processProducts = ProcessProduct::all();
        $selected = $group->groupingProcesses->pluck('process_product_id')->toArray();
        return view('groups.edit', compact('group','processProducts','selected'));
    }

    // Update group
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'process_products' => 'array',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Sync processes
        $group->groupingProcesses()->delete();
        if($request->has('process_products')) {
            foreach($request->process_products as $pp_id){
                GroupingProccess::create([
                    'group_id' => $group->id,
                    'process_product_id' => $pp_id
                ]);
            }
        }

        return redirect()->route('groups.index')->with('success','Group updated.');
    }

    // Delete group
    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('groups.index')->with('success','Group deleted.');
    }
}
