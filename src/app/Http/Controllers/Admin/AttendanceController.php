<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'previousDate' => $date->copy()->subDay()->format('Y/m/d'),
            'nextDate' => $date->copy()->addDay()->format('Y/m/d'),
        ]);
    }

    /**
     * Show user attendance monthly
     */
    public function show(Request $request, User $user)
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now();

        // 勤怠データ取得
        $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
        ->orderBy('date')
        ->get()
        ->keyBy(function ($attendance) {
            return $attendance->date->toDateString(); // 'Y-m-d' の形式でキー化
        });

        // 月の全日付を取得
        $period = CarbonPeriod::create($month->copy()->startOfMonth(), $month->copy()->endOfMonth());

        return view('admin.attendance.staff.show', [
            'user' => $user,
            'attendances' => $attendances, // Collection
            'period' => $period,           // CarbonPeriod
            'currentMonth' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
