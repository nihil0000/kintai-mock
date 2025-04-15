@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20 space-y-6">
        <section>
            <h2 class="text-xl font-bold mb-6 pl-4 border-l-4 border-black">勤怠詳細</h2>

            <form action="{{ route('attendance_correction.store', $attendance->id) }}" method="post">
                @csrf

                <div class="bg-white rounded p-2">
                    <table class="w-full table-auto border-collapse">
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">名前</th>
                            <td class="py-4 pl-24">{{ $attendance->user->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">日付</th>
                            <td class="py-4 pl-24">
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span class="ml-8">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </td>
                        </tr>
                        <tr class="border-b">
                            <th class="py-4 w-1/3 pl-16 text-left">出勤・退勤</th>
                            <td class="py-4 pl-24">
                                <input type="time" name="requested_clock_in"
                                    value="{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}"
                                    class="w-28 text-center border rounded py-1 px-2 mr-8" />
                                〜
                                <input type="time" name="requested_clock_out"
                                    value="{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}"
                                    class="w-28 text-center border rounded py-1 px-2 ml-8" />
                            </td>
                        </tr>
                        @foreach ($attendance->breaks as $i => $break)
                            <tr class="border-b">
                                <th class="py-4 w-1/3 pl-16 text-left">休憩{{ $i + 1 }}</th>
                                <td class="py-4 pl-24">
                                    <input type="time" name="requested_breaks[start][]" value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}"
                                        class="w-28 text-center border rounded py-1 px-2 mr-8" />
                                    〜
                                    <input type="time" name="requested_breaks[end][]" value="{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}"
                                        class="w-28 text-center border rounded py-1 px-2 ml-8" />
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th class="py-4 w-1/3 pl-16 text-left">備考</th>
                            <td class="py-4 pl-24">
                                <textarea name="note"
                                    class="w-full text-sm max-w-md border rounded py-2 px-3 resize-none">{{ $attendance->note }}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="text-center">
                    <button type="submit"
                        class="bg-black text-white text-lg font-bold w-28 py-2 mt-20 mb-4 rounded hover:bg-gray-700">
                        修正
                    </button>
                </div>

            </form>
        </section>
    </div>
</main>
@endsection
