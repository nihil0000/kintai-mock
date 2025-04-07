<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'clock_out', 'status', 'note'
    ];

    protected $casts = [
        'status' => AttendanceStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
