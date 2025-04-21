@extends('layouts.app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20">
        <section class="space-y-6">
            <h2 class="text-2xl font-bold mb-12 pl-4 border-l-4 border-black">申請一覧</h2>

            <div class="flex space-x-8">
                <a href="{{ route('stamp_correction_request.index', ['status' => 'pending']) }}"
                    class="font-semibold hover:text-black
                        {{ request()->query('status', 'pending') === 'pending'
                            ? 'border-b-2 border-black'
                            : 'text-gray-500'}}">
                    承認待ち
                </a>
                <a href="{{ route('stamp_correction_request.index', ['status' => 'approved']) }}"
                    class="font-semibold hover:text-black
                        {{ request()->query('status') === 'approved'
                            ? 'border-b-2 border-black'
                            : 'text-gray-500' }}">
                    承認済み
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border border-gray-300 bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2">状態</th>
                            <th class="p-2">名前</th>
                            <th class="p-2">対象日</th>
                            <th class="p-2">申請理由</th>
                            <th class="p-2">申請日</th>
                            <th class="p-2">詳細</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($correctionRequests as $correctionRequest)
                            <tr>
                                <td class="p-2">{{ $correctionRequest->status->label() }}</td>
                                <td class="p-2">{{ $correctionRequest->user->name }}</td>
                                <td class="p-2">{{ \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('Y/m/d') }}</td>
                                <td class="p-2">{{ $correctionRequest->note }}</td>
                                <td class="p-2">{{ $correctionRequest->created_at->format('Y/m/d') }}</td>
                                <td class="p-2">
                                    @auth('web')
                                        <a href="{{ route('attendance.show', optional($correctionRequest->attendance)->id) }}"
                                            class="text-blue-600 hover:underline">
                                            詳細
                                        </a>
                                    @endauth

                                    @auth('admins')
                                        <a href="{{ route('admin.request.show', $correctionRequest->id) }}"
                                            class="text-blue-600 hover:underline">
                                            詳細
                                        </a>
                                    @endauth
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
