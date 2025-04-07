<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = ['comment'];

    public function attendance_correction_request()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }
}
