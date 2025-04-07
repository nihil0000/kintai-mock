<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case OutsideWorking = 'outside_working';
    case Working        = 'working';
    case OnBreak        = 'on_break';
    case LeftWork       = 'left_work';

    public function label(): string
    {
        return match ($this) {
            self::OutsideWorking => '勤務外',
            self::Working        => '出勤中',
            self::OnBreak        => '休憩中',
            self::LeftWork       => '退勤済',
        };
    }
}
