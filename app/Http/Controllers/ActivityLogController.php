<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();
        $actions = ActivityLog::distinct()->pluck('action');

        return view('activity_logs.index', compact('logs', 'actions'));
    }
}
