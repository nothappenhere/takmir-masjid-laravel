<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\DutySchedule;
use App\Models\TaskAssignment;
use App\Models\Position;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic Statistics
        $stats = [
            'total_committees' => Committee::count(),
            'active_committees' => Committee::where('active_status', 'active')->count(),
            'total_positions' => Position::where('status', 'active')->count(),
            'today_duties' => DutySchedule::whereDate('duty_date', today())->count(),
            'pending_tasks' => TaskAssignment::where('status', 'pending')->count(),
            'completed_tasks' => TaskAssignment::where('status', 'completed')->count(),
        ];

        // Today's duties
        $todaySchedules = DutySchedule::with('committee')
            ->whereDate('duty_date', today())
            ->orderBy('start_time')
            ->get();

        // Recent committees
        $recentCommittees = Committee::with('position')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Upcoming duties (next 3 days)
        $upcomingDuties = DutySchedule::with('committee')
            ->whereDate('duty_date', '>', today())
            ->whereDate('duty_date', '<=', today()->addDays(3))
            ->orderBy('duty_date')
            ->orderBy('start_time')
            ->get();

        // Urgent tasks (due in 2 days or overdue)
        $urgentTasks = TaskAssignment::with(['committee', 'jobResponsibility'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function ($query) {
                $query->where('due_date', '<=', today()->addDays(2))
                    ->orWhere('status', 'overdue');
            })
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'todaySchedules',
            'recentCommittees',
            'upcomingDuties',
            'urgentTasks'
        ));
    }
}
