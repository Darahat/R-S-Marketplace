@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Privacy Policy</h1>
        <p class="text-gray-600 mb-6">This policy explains what data we collect, why we collect it, and how we protect it.</p>

        <div class="space-y-4 text-sm text-gray-700">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Information We Collect</h2>
                <p class="mt-1">We collect account, order, and payment-related information necessary to provide marketplace services.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">How We Use Information</h2>
                <p class="mt-1">We use your data to process orders, provide support, improve our services, and comply with legal obligations.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Data Security</h2>
                <p class="mt-1">We apply reasonable technical and organizational measures to protect your information from unauthorized access.</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('terms') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 transition">View Terms</a>
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary transition">Back to Home</a>
        </div>
    </div>
</div>
@endsection
