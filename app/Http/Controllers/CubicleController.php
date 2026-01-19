<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CubicleController extends Controller
{
    // Update cubicle status
    public function updateStatus(Request $request)
    {
        $request->validate([
            'cubicle_id' => 'required|integer|min:1|max:20',
            'status' => 'required|string|in:occupied,vacant',
        ]);

        $id = $request->cubicle_id;
        $status = strtolower($request->status);

        // Store in cache for 10 minutes
        Cache::put("cubicle_$id", $status, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'cubicle_id' => $id,
            'status' => $status
        ]);
    }

    // Get status for a specific cubicle
    public function getStatus($id)
    {
        if ($id < 1 || $id > 20) {
            return response()->json(['error' => 'Invalid cubicle ID'], 400);
        }

        $status = Cache::get("cubicle_$id", "vacant");

        return response()->json([
            'cubicle_id' => (int)$id,
            'status' => $status
        ]);
    }

    // Get all cubicles status
    public function getAllStatus()
    {
        $statuses = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $statuses[$i] = Cache::get("cubicle_$i", "vacant");
        }

        return response()->json(['cubicles' => $statuses]);
    }

    // Dashboard view
    public function dashboard()
    {
        return view('dashboard');
    }
}