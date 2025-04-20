@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20">
        <section class="space-y-6">
            <h2 class="text-2xl font-bold pl-4 border-l-4 border-black">
                {{ $currentDate->format('Y年m月d日') }}の勤怠
            </h2>

            <!-- 月選択 -->
            <div class="flex items-center justify-between mb-4 bg-white py-2 px-4 rounded">
                <a href="{{ route('admin.attendance.index', ['date' => $previousDate]) }}" class="text-gray-600 hover:underline">&lt 前日</a>

                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="20" height="20"
                        class="inline-block text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-lg font-semibold">{{ $currentDate->format('Y/m/d') }}</span>
                </div>
                <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}" class="text-gray-600 hover:underline">翌日 &gt</a>
            </div>

            <!-- 勤怠テーブル -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border border-gray-300 bg-white">
                    <thead class="bg-gray-100 border-b-4">
                        <tr>
                            <th class="p-2">名前</th>
                            <th class="p-2">出勤</th>
                            <th class="p-2">退勤</th>
                            <th class="p-2">休憩</th>
                            <th class="p-2">合計</th>
                            <th class="p-2">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td class="p-2">
                                    {{ $attendance->user->name }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                                </td>
                                <td class="p-2">
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance->break_time ?? '-' }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance->total_time ?? '-' }}
                                </td>
                                <td class="p-2">
                                    <a href="{{ route('attendance.show', $attendance->id) }}" class="text-blue-600 hover:underline">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
@endsection
