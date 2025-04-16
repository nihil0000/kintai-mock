<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'clock_in', 'clock_out', 'status', 'note'
    ];

    protected $casts = [
        'status' => AttendanceStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendance_correction_request()
    {
        return $this->hasOne(AttendanceCorrectionRequest::class);
    }

    /**
     * Calculate total break time in seconds
     * Iterates through all breaks and sums up the duration of each completed break
     * @return int Total break time in seconds
     */
    protected function getBreakSeconds(): int
    {
        $totalSeconds = 0;

        foreach ($this->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                $totalSeconds += $end->diffInSeconds($start);
            }
        }

        return $totalSeconds;
    }

    /**
     * Format break time as HH:MM
     * Uses the total break seconds to create a human-readable time format
     * @return string Formatted break time (HH:MM)
     */
    public function getBreakTimeAttribute()
    {
        return gmdate('H:i', $this->getBreakSeconds());
    }

    /**
     * Calculate and format total work time
     * Subtracts break time from total time between clock in and clock out
     * Returns null if either clock in or clock out is missing
     * @return string|null Formatted work time (HH:MM) or null if incomplete
     */
    public function getTotalTimeAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $start = Carbon::parse($this->clock_in);
        $end = Carbon::parse($this->clock_out);
        $workSeconds = $end->diffInSeconds($start);

        $actualWorkSeconds = $workSeconds - $this->getBreakSeconds();

        return gmdate('H:i', $actualWorkSeconds);
    }
}
