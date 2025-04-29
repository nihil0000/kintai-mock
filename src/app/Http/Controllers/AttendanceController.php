<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionFormRequest;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceCorrectionRequestStatus;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    /**
     * Attendance view
     */
    public function create()
    {
        // 当日かつログインユーザーの勤怠情報を取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->first();

        // 勤怠レコードがない場合は初期状態（勤務外）とする
        $attendanceStatus = $attendance ? $attendance->status->value : AttendanceStatus::BeforeWork->value;

        // Blade テンプレートへ渡す
        return view('attendance.create', compact('attendance', 'attendanceStatus'));
    }

    /**
     * Start work
     */
    public function startWork()
    {
        Attendance::create(
            [
                'user_id'   => auth()->id(),
                'date' => now()->toDateString(),
                'clock_in' => now(),
                'status'     => AttendanceStatus::Working->value,
            ]
        );

        return redirect()->back();
    }

    /**
     * Start break
     */
    public function startBreak()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->firstOrFail(); // Returns 404 error if no attendance record exists for today

        $attendance->breaks()->create([
            'break_start' => now(),
        ]);

        $attendance->update([
            'status' => AttendanceStatus::OnBreak->value,
        ]);

        return redirect()->back();
    }

    /**
     * End break
     */
    public function endBreak()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->firstOrFail(); // Returns 404 error if no attendance record exists for today

        // Get the latest unclosed break
        $latestUnclosedBreak = $attendance->breaks()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        if ($latestUnclosedBreak) {
            $latestUnclosedBreak->update([
                'break_end' => now(),
            ]);
        }

            $attendance->update([
            'status'         => AttendanceStatus::Working->value,
        ]);

        return redirect()->back();
    }

    /**
     * End work
     */
    public function endWork()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->firstOrFail(); // Returns 404 error if no attendance record exists for today

        $attendance->update([
            'clock_out' => now(),
            'status'   => AttendanceStatus::AfterWork->value,
        ]);

        return redirect()->back();
    }

    /**
     * Display attendance list
     */
    public function index(Request $request)
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now();

        // 勤怠データ取得
        $attendances = Attendance::where('user_id', auth()->id())
        ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
        ->orderBy('date')
        ->get()
        ->keyBy(function ($attendance) {
            return $attendance->date->toDateString(); // 'Y-m-d' の形式でキー化
        });

        // 月の全日付を取得
        $period = CarbonPeriod::create($month->copy()->startOfMonth(), $month->copy()->endOfMonth());

        return view('attendance.index', [
            'attendances' => $attendances, // Collection
            'period' => $period,           // CarbonPeriod
            'currentMonth' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /**
     * Show attendance
     */
    public function show(Attendance $attendance)
    {
        // Check if the user is authorized to view the attendance record
        $this->authorize('view', $attendance);

        // Get the correction request and initialize breaks collection
        $correction = $attendance->attendance_correction_request;
        $breaks = collect();

        // Attendance
        $clockIn = optional($correction)->requested_clock_in
        ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i')
        : \Carbon\Carbon::parse($attendance->clock_in)->format('H:i');

        $clockOut = optional($correction)->requested_clock_out
            ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i')
            : ($attendance->clock_out
                ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                : '');

        // Breaks
        // If there's a correction request with breaks, use those values
        if (optional($correction)->requested_breaks) {
            $decoded = json_decode($correction->requested_breaks, true);

            foreach ($decoded as $break) {
                $breaks->push([
                    'break_start' => $break['break_start'] ?? '',
                    'break_end' => $break['break_end'] ?? '',
                ]);
            }
        } else {
            // Otherwise, use the original break times from the attendance record
            foreach ($attendance->breaks as $break) {
                $breaks->push([
                    'break_start' => $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '',
                    // 'break_start' => optional($break->break_start)->format('H:i'),
                    // 'break_end' => optional($break->break_end)->format('H:i'),
                    'break_end'   => $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '',
                ]);
            }
        }

        return view('attendance.show', compact('attendance', 'breaks', 'clockIn', 'clockOut'));
    }

    /**
     * Store attendance correction
     */
    public function store(AttendanceCorrectionFormRequest $request, Attendance $attendance)
    {
        $this->authorize('view', $attendance);

        // 休憩時間を JSON に整形（optional）
        $requestedBreaksRaw = $request->input('requested_breaks', []);
        $requestedBreaks = [];

        foreach ($requestedBreaksRaw['start'] ?? [] as $i => $start) {
            $end = $requestedBreaksRaw['end'][$i] ?? null;

            if ($start && $end) {
                $requestedBreaks[] = [
                    'break_start' => $start,
                    'break_end' => $end,
                ];
            }
        }

        // 勤怠修正申請の登録
        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'requested_clock_in' => $request->requested_clock_in
                ? \Carbon\Carbon::parse($attendance->date)->setTimeFromTimeString($request->requested_clock_in)
                : null,
            'requested_clock_out' => $request->requested_clock_out
                ? \Carbon\Carbon::parse($attendance->date)->setTimeFromTimeString($request->requested_clock_out)
                : null,
            'requested_breaks' => json_encode($requestedBreaks),
            'note' => $request->note,
            'status' => AttendanceCorrectionRequestStatus::Pending,
        ]);

        return redirect()->route('attendance.show', $attendance->id);
    }
}
