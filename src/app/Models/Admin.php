<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $guard = 'admins';

    protected $fillable = [
        'name', 'email', 'password',
    ];

    public function approval_logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function approval_log()
    {
        return $this->belongsTo(ApprovalLog::class);
    }
}
