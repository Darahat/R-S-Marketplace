<?php
namespace App\Http\Controllers;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeWebhookService $service)
    {
    }

    public function handle(Request $request)
    {
        return $this->service->handle($request);
    }
}
