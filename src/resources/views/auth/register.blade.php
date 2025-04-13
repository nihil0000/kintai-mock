@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-xl mx-auto my-20 space-y-6">
        <h1 class="text-2xl font-semibold text-center">会員登録</h1>

        <!-- register form -->
        <div>
            <form action="{{ route('register.create') }}" method="post" novalidate class="space-y-4">
                @csrf

                <!-- user name -->
                <section>
                    <label for="user_name" class="block mb-1">名前</label>

                    <input type="text" name="name" id="user_name" value="{{ old('name') }}"
                        class="w-full h-10 border border-gray-400 rounded px-3 text-sm">

                    <!-- validation -->
                    @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                <!-- email -->
                <section>
                    <label for="email" class="block mb-1">メールアドレス</label>

                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full h-10 border border-gray-400 rounded px-3 text-sm">

                    <!-- validation -->
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                <!-- password -->
                <section>
                    <label for="password" class="block mb-1">パスワード</label>

                    <input type="password" name="password" id="password"
                        class="w-full h-10 border border-gray-400 px-3 text-sm rounded">

                    <!-- validation -->
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                <!-- confirm password -->
                <section>
                    <label for="confirmed_password" class="block mb-1">確認用パスワード</label>

                    <input type="password" id="confirmed_password" name="password_confirmation"
                        class="w-full h-10 border border-gray-400 px-3 rounded">

                    <!-- validation -->
                    @error('password_confirmation')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                <!-- submit -->
                <section>
                    <button type="submit"
                        class="w-full h-10 bg-black text-white rounded hover:bg-gray-700 transition text-sm mt-8">
                        登録する
                    </button>
                </section>

                <!-- link to login -->
                <a href="{{ route('login.create') }}"
                    class="block text-center text-sm text-blue-600 hover:underline mt-4">
                    ログインはこちら
                </a>
            </form>
        </div>
    </div>
</main>
@endsection
