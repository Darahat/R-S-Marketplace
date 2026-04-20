@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Customer Support</h1>
        <p class="text-gray-600 mb-6">Need help with your order? Our support team is here to help you quickly.</p>

        <div class="space-y-4">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Email Support</h2>
                <p class="text-sm text-gray-600 mt-1">For order issues, refunds, and delivery queries.</p>
                <a class="text-blue-600 hover:underline text-sm mt-2 inline-block" href="mailto:{{ config('mail.from.address', 'support@example.com') }}?subject=Order%20Support%20Request">{{ config('mail.from.address', 'support@example.com') }}</a>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <h2 class="font-semibold text-gray-800">Before You Contact</h2>
                <ul class="list-disc pl-5 text-sm text-gray-600 mt-2 space-y-1">
                    <li>Keep your order number ready.</li>
                    <li>Describe the issue clearly (payment, shipment, return, etc.).</li>
                    <li>Attach screenshots if relevant.</li>
                </ul>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('customer.orders') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary transition">Back to My Orders</a>
        </div>
    </div>
</div>
@endsection
