@extends('layouts.app')

@section('content')
<main class="flex-grow px-4">
    <div class="mx-auto mt-40 text-center max-w-3xl w-full">
        <h1 class="text-lg mb-12">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </h1>

        <a href="http://localhost:8025"
            target="_brank"
            class="inline-block px-6 py-3 bg-gray-300 rounded hover:bg-gray-400 transition">
            メールを確認する
        </a>

        <form method="post" action="{{ route('verification.send') }}" class="mt-4">
            @csrf

            <button type="submit" class="text-sm text-blue-500 hover:underline">
                認証メールを再送する
            </button>
        </form>

        @if (session('status'))
            <div class="mt-4 text-green-500 text-sm">
                {{ session('status') }}
            </div>
        @endif
    </div>
</main>
@endsection
