<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'admin_id',
        'approved_at',
        'action',
        'comment',
    ];

    public function attendance_correction_request()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
