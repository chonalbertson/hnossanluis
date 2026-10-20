<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('activity_logs.view'), 403);

        $query = ActivityLog::query()->with('user');

        if ($request->filled('module')) {
            $query->where('module', $request->string('module')->toString());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.trim($request->string('action')->toString()).'%');
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('action', 'like', $term)
                    ->orWhere('ip_address', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from')->toString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to')->toString());
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $modules = ActivityLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
        $users = User::query()->whereNotNull('role_id')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'users' => $users,
        ]);
    }

    public function show(Request $request, ActivityLog $activityLog): View
    {
        abort_unless($request->user()?->hasPermission('activity_logs.view'), 403);

        $activityLog->load('user');

        return view('admin.activity-logs.show', [
            'log' => $activityLog,
        ]);
    }
}
