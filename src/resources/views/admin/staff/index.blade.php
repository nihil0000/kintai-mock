@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-4xl mx-auto my-20">
        <section class="space-y-6">
            <h2 class="text-2xl font-bold pl-4 border-l-4 border-black">
                スタッフ一覧
            </h2>

            <!-- 勤怠テーブル -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border border-gray-300 bg-white">
                    <thead class="bg-gray-100 border-b-4">
                        <tr>
                            <th class="p-2">名前</th>
                            <th class="p-2">メールアドレス</th>
                            <th class="p-2">月次勤怠</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b-2">
                                <td class="p-2">
                                    {{ $user->name }}
                                </td>
                                <td class="p-2">
                                    {{ $user->email }}
                                </td>
                                <td class="p-2">
                                    <a href="{{ route('admin.attendance.show', $user->id) }}" class="text-blue-600 hover:underline">詳細</a>
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
