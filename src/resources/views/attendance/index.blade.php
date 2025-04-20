@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20">
        <section class="space-y-6">
            <h2 class="text-2xl font-bold pl-4 border-l-4 border-black">勤怠一覧</h2>

            <!-- 月選択 -->
            <div class="flex items-center justify-between mb-4 bg-white py-2 px-4 rounded">
                <a href="{{ route('attendance.index', ['month' => $previousMonth]) }}" class="text-gray-600 hover:underline">&lt 前月</a>

                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="20" height="20"
                        class="inline-block text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-lg font-semibold">{{ $currentMonth->format('Y/m') }}</span>
                </div>
                <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="text-gray-600 hover:underline">翌月 &gt</a>
            </div>

            <!-- 勤怠テーブル -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border border-gray-300 bg-white">
                    <thead class="bg-gray-100 border-b-4">
                        <tr>
                            <th class="p-2">日付</th>
                            <th class="p-2">出勤</th>
                            <th class="p-2">退勤</th>
                            <th class="p-2">休憩</th>
                            <th class="p-2">合計</th>
                            <th class="p-2">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($period as $date)
                            @php
                                $attendance = $attendances->get($date->toDateString());
                            @endphp
                            <tr class="border-b-2">
                                <td class="p-2">
                                    {{ $date->isoFormat('MM/DD(dd)') }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                                </td>
                                <td class="p-2">
                                {{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance?->break_time ?? '-' }}
                                </td>
                                <td class="p-2">
                                    {{ $attendance?->total_time ?? '-' }}
                                </td>
                                <td class="p-2">
                                    @if ($attendance)
                                        <a href="{{ route('attendance.show', $attendance->id) }}" class="text-blue-600 hover:underline">詳細</a>
                                    @else
                                        -
                                    @endif
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
