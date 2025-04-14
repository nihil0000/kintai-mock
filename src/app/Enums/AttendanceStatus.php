<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case BeforeWork = 'before_work';
    case Working        = 'working';
    case OnBreak        = 'on_break';
    case AfterWork       = 'after_work';

    public function label(): string
    {
        return match ($this) {
            self::BeforeWork => '勤務外',
            self::Working        => '出勤中',
            self::OnBreak        => '休憩中',
            self::AfterWork       => '退勤済',
        };
    }
}
