@extends('layouts/app')

@section('content')
<main class="flex-grow px-4">
    <div class="w-full max-w-xl mx-auto my-20 space-y-6">
        <h1 class="text-2xl font-semibold text-center">管理者ログイン</h1>

        <!-- login form -->
        <section>
            <form action="{{ route('admin.login.store') }}" method="post" novalidate class="space-y-4">
                @csrf

                <!-- email -->
                <div>
                    <label for="email" class="block mb-1">メールアドレス</label>

                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full h-10 border border-gray-400 rounded px-3 text-sm">

                    <!-- validation -->
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- password -->
                <div>
                    <label for="password" class="block mb-1">パスワード</label>

                    <input type="password" name="password" id="password"
                        class="w-full h-10 border border-gray-400 rounded px-3 text-sm">

                    <!-- validation -->
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- submit -->
                <div>
                    <button type="submit"
                        class="w-full bg-black text-white h-10 rounded hover:bg-gray-700 transition text-sm mt-8">
                        管理者ログインする
                    </button>
                </div>

            </form>
        </section>
    </div>
</main>
@endsection
