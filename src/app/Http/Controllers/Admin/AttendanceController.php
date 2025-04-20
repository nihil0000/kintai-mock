<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    // Export csv
    public function exportCsv(Request $request, User $user): StreamedResponse
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now();

        // 勤怠データを取得し、日付をキーにマッピング
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn($a) => $a->date->toDateString());

        $period = CarbonPeriod::create($month->copy()->startOfMonth(), $month->copy()->endOfMonth());

        $filename = $user->name . '_勤怠_' . $month->format('Y_m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($attendances, $period) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM（Excel対応）
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // ヘッダ行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($period as $date) {
                $attendance = $attendances->get($date->toDateString());

                fputcsv($handle, [
                    $date->isoFormat('Y/MM/DD(dd)'),
                    $attendance?->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '-',
                    $attendance?->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    $attendance?->break_time ?? '-',
                    $attendance?->total_time ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
