<?php

namespace App\Http\Controllers;

use App\Models\PlanProductCo;

class PlanProductCoController extends Controller
{
    public function updateLockStatus(PlanProductCo $planProductCo, $is_locked)
    {
        $planProductCo->is_locked = filter_var($is_locked, FILTER_VALIDATE_BOOLEAN);
        $planProductCo->save();

        return response()->json(['message' => 'Lock status updated successfully', 'is_locked' => $planProductCo->is_locked]);
    }
}
