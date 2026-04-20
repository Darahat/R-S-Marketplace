@extends('frontend_view.layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center px-4 py-8 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <img class="mx-auto h-10 w-auto" src="{{ asset('images/logo/logo.png') }}" alt="R&SMarketPlace">
        </a>
        <h2 class="mt-4 text-2xl font-bold text-gray-900">Forgot your password?</h2>
        <p class="mt-2 text-sm text-gray-600">Enter your customer account email and we will send a reset link.</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-6 px-5 shadow rounded-lg">
            @if (session('status'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-primary">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary transition-colors">
                    Email Password Reset Link
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <a href="{{ route('home', ['auth' => 'login']) }}" class="text-primary hover:text-secondary">Back to login</a>
            </div>
        </div>
    </div>
</div>
@endsection
