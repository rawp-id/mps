<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['plan', 'product', 'process', 'machine', 'operation'])
            ->get();
        return view('results.index', compact('schedules'));
    }
}
