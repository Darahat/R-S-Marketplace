<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagePaymentMethodRequest;
use App\Repositories\UserPaymentMethodRepository;
use App\Services\PaymentMethodService;
use App\Policies\PaymentMethodPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;

    protected $paymentMethodExists = null;
    /**
     * Display user's saved payment methods
     */
    public function __construct(private PaymentMethodService $service, private UserPaymentMethodRepository $repo){}
    public function index()
    {


        return view('frontend_view.pages.payment_methods.index', [
            'data' => [
                'title' => 'Saved Payment Methods',
            ],
            'paymentMethods' => $this->repo->listForUser(Auth::id()),
        ]);
    }

    private function handle(string $action, ManagePaymentMethodRequest $request){
        $method = $this->repo->findForUser(Auth::id(),$request->payment_method_id);
        $this->authorize('manage', $method);
        $this->service->handle($action,Auth::id(), $method);
 return back()->with('success', 'Payment method updated successfully.');
    }
    /**
     * Set a payment method as default
     */
    public function setDefault(ManagePaymentMethodRequest $request)
    {
         return $this->handle('delete', $request);
    }

    /**
     * Delete a saved payment method
     */
    public function destroy(ManagePaymentMethodRequest $request)
    {
       return $this->handle('delete', $request);
    }

    /**
     * Get saved payment methods as JSON (for checkout)
     */
    public function getSavedMethods()
    {
        $methods = $this->repo->listForUser(Auth::id());



        return response()->json($this->service->formatForCheckout($methods));
    }
}
