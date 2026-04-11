@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Terms and Conditions</h1>
        <p class="text-gray-600 mb-6">These terms explain the rules for using our marketplace and placing orders.</p>

        <div class="space-y-4 text-sm text-gray-700">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Account Responsibility</h2>
                <p class="mt-1">You are responsible for keeping your account credentials secure and for activities performed under your account.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Orders and Payments</h2>
                <p class="mt-1">Orders are processed after successful payment verification. We reserve the right to cancel suspicious or invalid transactions.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Returns and Refunds</h2>
                <p class="mt-1">Return and refund requests are handled according to our return policy and applicable consumer protections.</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('privacy.policy') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 transition">View Privacy Policy</a>
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary transition">Back to Home</a>
        </div>
    </div>
</div>
@endsection
