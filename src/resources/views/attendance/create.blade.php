@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-xl mx-auto my-40 space-y-6">
        <section class="space-y-8">
            <div class="text-center">
                <p class="inline-block bg-[#C8C8C8] text-[#696969] text-lg rounded-2xl px-4 py-1">
                    <!-- attendance status -->
                    {{ \App\Enums\AttendanceStatus::from($attendanceStatus)->label() }}
                </p>
            </div>
            <div class="text-center space-y-8">
                <p class="text-4xl">{{ now()->isoFormat('YYYY年M月D日（ddd）') }}</p>
                <p class="text-6xl font-bold">{{ now()->format('H:i') }}</p>
            </div>

            @switch($attendanceStatus)

                {{-- before_work --}}
                @case(\App\Enums\AttendanceStatus::BeforeWork->value)
                    <div class="text-center">
                        <div class="mt-12">
                            {{-- button --}}
                            <form action="{{ route('attendance.start') }}" method="post">
                                @csrf

                                <button type="submit"
                                    class="bg-black text-white text-3xl font-bold px-16 py-4 rounded-xl hover:opacity-80">
                                    出勤
                                </button>
                            </form>
                        </div>
                    </div>
                    @break

                {{-- working --}}
                @case(\App\Enums\AttendanceStatus::Working->value)
                    <div class="text-center">
                        <div class="space-x-12 mt-12">
                            {{-- button (end_work) --}}
                            <form action="{{ route('attendance.end') }}" method="post" class="inline-block">
                                @csrf

                                <button type="submit"
                                    class="bg-black text-white text-3xl font-bold w-40 py-4 rounded-xl hover:opacity-80">
                                    退勤
                                </button>
                            </form>

                            {{-- button (start_break) --}}
                            <form action="{{ route('attendance.start_break') }}" method="post" class="inline-block">
                                @csrf

                                <button type="submit"
                                    class="bg-white text-black text-3xl font-bold w-40 py-4 rounded-xl hover:bg-gray-300">
                                    休憩入
                                </button>
                            </form>
                        </div>
                    </div>
                    @break

                {{-- on_break --}}
                @case(\App\Enums\AttendanceStatus::OnBreak->value)
                    <div class="text-center">
                        <div class="mt-12">
                            {{-- button (end_break) --}}
                            <form action="{{ route('attendance.end_break') }}" method="post">
                                @csrf

                                <button type="submit"
                                    class="bg-white text-black text-3xl font-bold w-40 py-4 rounded-xl hover:bg-gray-300">
                                    休憩戻
                                </button>
                            </form>
                        </div>
                    </div>
                    @break

                {{-- after_work --}}
                @case(\App\Enums\AttendanceStatus::AfterWork->value)
                    <div class="text-center">
                        <p class="mt-12 text-lg">お疲れ様でした。</p>
                    </div>
                    @break

            @endswitch
        </section>
    </div>
</main>
@endsection

