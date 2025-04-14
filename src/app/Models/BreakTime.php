<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'break_start',
        'break_end'
    ];

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
