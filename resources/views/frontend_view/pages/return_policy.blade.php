@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Return Policy</h1>
        <p class="text-gray-600 mb-6">We want you to shop with confidence. Please review our return rules below.</p>

        <div class="space-y-4 text-sm text-gray-700">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Return Window</h2>
                <p class="mt-1">Returns are accepted within 7 days of delivery for eligible items.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Condition Requirements</h2>
                <p class="mt-1">Items must be unused and in original packaging with all accessories.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">How to Request a Return</h2>
                <p class="mt-1">Contact support with your order number and reason for return. Our team will guide you through the process.</p>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Refund Timeline</h2>
                <p class="mt-1">Approved refunds are processed to the original payment method within 5-10 business days.</p>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('support') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary transition">Contact Support</a>
            <a href="{{ route('customer.orders') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Back to My Orders</a>
        </div>
    </div>
</div>
@endsection
