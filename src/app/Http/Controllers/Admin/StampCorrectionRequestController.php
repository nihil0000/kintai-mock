<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;
use App\Models\ApprovalLog;
use App\Enums\AttendanceCorrectionRequestStatus;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $correctionRequests = AttendanceCorrectionRequest::with('user', 'attendance')
            ->where('status', $status)
            ->orderBy('requested_clock_in')
            ->get();

        return view('stamp_correction_request.index', compact('correctionRequests', 'status'));
    }

    public function show(AttendanceCorrectionRequest $attendance_correction_request)
    {
        $attendance = $attendance_correction_request->attendance;
        $breaks = collect();

        // Attendance
        $clockIn = $attendance_correction_request->requested_clock_in
            ? \Carbon\Carbon::parse($attendance_correction_request->requested_clock_in)->format('H:i')
            : (optional($attendance)->clock_in
                ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                : '');

        // 退勤時間
        $clockOut = $attendance_correction_request->requested_clock_out
            ? \Carbon\Carbon::parse($attendance_correction_request->requested_clock_out)->format('H:i')
            : (optional($attendance)->clock_out
                ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                : '');

        // 休憩時間
        if ($attendance_correction_request->requested_breaks) {
            $decoded = json_decode($attendance_correction_request->requested_breaks, true);
            foreach ($decoded as $break) {
                $breaks->push([
                    'break_start' => $break['break_start'] ?? '',
                    'break_end' => $break['break_end'] ?? '',
            ]);
            }
        } else {
            foreach ($attendance->breaks ?? [] as $break) {
                $breaks->push([
                    'break_start' => $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '',
                    'break_end' => $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '',
                ]);
            }
        }

        return view('admin.stamp_correction_request.show', compact('attendance', 'breaks', 'clockIn', 'clockOut'));
    }

    // Update correction request
    public function update(AttendanceCorrectionRequest $attendance_correction_request)
    {
        $attendance_correction_request->update([
            'status' => AttendanceCorrectionRequestStatus::Approved->value,
        ]);

        // 承認ログの作成
        ApprovalLog::create([
            'attendance_correction_request_id' => $attendance_correction_request->id,
            'admin_id' => auth('admins')->id(), // 管理者ログイン中が前提
            'approved_at' => \Carbon\Carbon::now(),
            'action' => 'approved',
            'comment' => null, // 管理者コメントを入力させたいならフォームで追加
        ]);
        return redirect()->back();
    }
}
