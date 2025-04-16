<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AttendanceCorrectionRequestStatus;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable =[
        'attendance_id', 'user_id', 'requested_clock_in', 'requested_clock_out', 'requested_breaks', 'note', 'status',
    ];

    protected $casts = [
        'status' => AttendanceCorrectionRequestStatus::class,
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_log()
    {
        return $this->belongsTo(ApprovalLog::class);
    }
}
