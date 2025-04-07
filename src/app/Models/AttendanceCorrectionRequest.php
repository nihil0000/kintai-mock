<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AttendanceCorrectionRequestStatus;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable =[
        'requested_clock_in', 'requested_clock_out', 'requested_breaks'
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
}
