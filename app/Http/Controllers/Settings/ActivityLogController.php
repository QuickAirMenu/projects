<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()->with('causer')->latest('id');

        if ($request->filled('user_id')) {
            $query->where('causer_type', User::class)->where('causer_id', $request->integer('user_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event'));
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($w) => $w->where('description', 'like', $q)->orWhere('subject_type', 'like', $q));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $activities = $query->paginate(30)->withQueryString();

        $users = User::whereIn('id', Activity::query()
            ->where('causer_type', User::class)
            ->whereNotNull('causer_id')
            ->distinct()
            ->pluck('causer_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $events = Activity::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event');

        return view('settings.audit-log', [
            'activities' => $activities,
            'users' => $users,
            'events' => $events,
            'filters' => $request->only(['user_id', 'event', 'q', 'from', 'to']),
        ]);
    }
}
