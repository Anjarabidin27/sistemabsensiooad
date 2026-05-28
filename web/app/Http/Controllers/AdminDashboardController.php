<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\Attendance;
use App\Services\AiEngineService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private AiEngineService $aiEngine;
    private SettingsService $settings;

    public function __construct(AiEngineService $aiEngine, SettingsService $settings)
    {
        $this->aiEngine = $aiEngine;
        $this->settings = $settings;
    }

    public function index()
    {
        // Today range
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        // 1. Stats Cards
        $totalStudents = Student::count();
        $activeStudents = Student::where('is_active', true)->count();
        
        $totalCourses = Course::count();
        
        $attendancesToday = Attendance::whereBetween('check_in_at', [$todayStart, $todayEnd])->get();
        
        $presentToday = $attendancesToday->where('status', 'present')->count();
        $lateToday = $attendancesToday->where('status', 'late')->count();
        $rejectedToday = $attendancesToday->where('status', 'rejected')->count();
        $totalCheckinsToday = $attendancesToday->count();

        // Attendance rate (present + late vs active students)
        $attendedCount = $presentToday + $lateToday;
        $attendanceRate = $activeStudents > 0 ? ($attendedCount / $activeStudents) * 100 : 0;

        // 2. Recent Activity (Latest 8 logs)
        $recentAttendances = Attendance::with(['student', 'course'])
            ->orderBy('check_in_at', 'desc')
            ->limit(8)
            ->get();

        // 3. Chart Data: Weekly attendance (last 7 days)
        $weeklyStats = DB::table('attendances')
            ->select(
                DB::raw('DATE(check_in_at) as date'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            )
            ->where('check_in_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Format chart data for Chart.js
        $chartLabels = [];
        $chartPresent = [];
        $chartLate = [];

        // Build 7 days structure to handle days with no data
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $label = Carbon::today()->subDays($i)->isoFormat('dddd');
            $chartLabels[] = $label;

            $dayData = $weeklyStats->firstWhere('date', $date);
            $chartPresent[] = $dayData ? (int) $dayData->present : 0;
            $chartLate[] = $dayData ? (int) $dayData->late : 0;
        }

        // 4. AI Engine health status
        $aiHealth = $this->aiEngine->health();

        return view('admin.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'totalCourses',
            'totalCheckinsToday',
            'presentToday',
            'lateToday',
            'rejectedToday',
            'attendanceRate',
            'recentAttendances',
            'chartLabels',
            'chartPresent',
            'chartLate',
            'aiHealth'
        ));
    }
}
