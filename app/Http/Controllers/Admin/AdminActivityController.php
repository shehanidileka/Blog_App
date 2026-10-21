<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\Activity;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('description', 'like', $term)
                    ->orWhere('user_name', 'like', $term)
                    ->orWhere('ip_address', 'like', $term));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('action', 'like', $request->type . '.%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity', [
            'logs'  => $logs,
            'types' => ['post', 'comment', 'category', 'user', 'role', 'permission', 'settings'],
        ]);
    }

    // Delete old entries
    public function prune(Request $request)
    {
        $data = $request->validate(['days' => ['required', 'integer', 'min:1', 'max:3650']]);

        $count = ActivityLog::where('created_at', '<', now()->subDays($data['days']))->delete();

        Activity::log('settings.log_pruned', "Activity log cleaned: $count entries older than {$data['days']} days deleted");

        return back()->with('success', "$count old entries deleted.");
    }
}