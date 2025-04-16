@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20 space-y-6">
        <section>
            <h2 class="text-2xl font-bold mb-6 pl-4 border-l-4 border-black">勤怠詳細</h2>

            <form action="{{ route('attendance_correction.store', $attendance->id) }}" method="post">
                @csrf

                @php
                    $isPending = optional($attendance->attendance_correction_request)->status === \App\Enums\AttendanceCorrectionRequestStatus::Pending;
                @endphp

                <div class="bg-white rounded p-2">
                    <table class="w-full table-auto border-collapse">
                        <!-- user name -->
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">名前</th>
                            <td class="py-4 pl-24">{{ $attendance->user->name }}</td>
                        </tr>

                        <!-- attedance date -->
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">日付</th>
                            <td class="py-4 pl-24">
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span class="ml-8">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </td>
                        </tr>

                        <!-- attendance time -->
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">出勤・退勤</th>
                            <td class="py-4 pl-24">
                                <input type="time" name="requested_clock_in"
                                    {{ $isPending ? 'readonly' : '' }}
                                    value="{{ old('requested_clock_in', $clockIn) }}"
                                    class="w-28 text-center border rounded py-1 px-2 mr-8" />
                                〜
                                <input type="time" name="requested_clock_out"
                                    {{ $isPending ? 'readonly' : '' }}
                                    value="{{ old('requested_clock_out', $clockOut) }}"
                                    class="w-28 text-center border rounded py-1 px-2 ml-8" />

                                <!-- validation message -->
                                @error('requested_clock_in')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>

                        <!-- break time -->
                        @foreach ($breaks as $i => $break)
                            <tr class="border-b">
                                <th class="py-4 w-1/3 pl-16 text-left">休憩{{ $i + 1 }}</th>
                                <td class="py-4 pl-24">
                                    <input type="time" name="requested_breaks[start][]"
                                        {{ $isPending ? 'readonly' : '' }}
                                        value="{{ old("requested_breaks.start.$i", $break['break_start']) }}"
                                        class="w-28 text-center border rounded py-1 px-2 mr-8" />
                                    〜
                                    <input type="time" name="requested_breaks[end][]"
                                        {{ $isPending ? 'readonly' : '' }}
                                        value="{{ old("requested_breaks.end.$i", $break['break_end']) }}"
                                        class="w-28 text-center border rounded py-1 px-2 ml-8" />

                                    <!-- validation message -->
                                    @error("requested_breaks.start.$i")
                                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach

                        <!-- note -->
                        <tr>
                            <th class="py-4 w-1/3 pl-16 text-left">備考</th>
                            <td class="py-4 pl-24">
                                <textarea name="note"
                                    class="w-full text-sm max-w-md border rounded py-2 px-3 resize-none"
                                    {{ $isPending ? 'readonly' : '' }}>{{ old('note', optional($attendance->attendance_correction_request)->note ?? $attendance->note) }}</textarea>

                                <!-- validation message -->
                                @error('note')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </div>

                @if ($isPending)
                    <p class="text-center text-red-500 mt-8">*承認待ちのため修正はできません。</p>
                @else
                    <div class="text-center">
                        <button type="submit"
                            class="bg-black text-white text-lg font-bold w-28 py-2 mt-20 mb-4 rounded hover:bg-gray-700">
                            修正
                        </button>
                    </div>
                @endif

            </form>
        </section>
    </div>
</main>
@endsection
