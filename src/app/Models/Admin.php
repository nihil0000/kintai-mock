<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    public function approval_logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function approval_log()
    {
        return $this->belongsTo(ApprovalLog::class);
    }
}
