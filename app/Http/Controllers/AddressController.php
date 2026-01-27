<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Repositories\UserAddressRepository;
use App\Http\Requests\UserAddressRequests;
use App\Services\AddressService;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\throwException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AddressController extends Controller
{
    use AuthorizesRequests;

    public function __construct( private UserAddressRepository $repo, private AddressService $service){}

    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        $user = Auth::user();
        $shippingAddresses =$this->repo->getUserShippingAddress($user->id);
        $billingAddresses = $this->repo->getUserBillingAddress($user->id);
        return view('backend_panel_view_customer.pages.address_list', compact('shippingAddresses', 'billingAddresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        return view('backend_panel_view_customer.pages.create_edit_address', ['district' => $this->repo->getDistricts()]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(UserAddressRequests $request)
    {
        $user = Auth::user();
        // return $request->all();
       $validated = $request->validated();

        // If setting as default, remove default status from other addresses of same type
        if ($request->has('is_default') && $request->is_default) {
            $this->repo->unsetOtherDefaults($user->id,$validated->address_type,$validated->id,true);

        }
        try{
        $this->repo->createAddress($validated);
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address added successfully!');
        }catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error processing your order: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Update the specified address in storage.
     */
    public function update(UserAddressRequests $request, $address_id, $user_id)
    {
        $address = $this->repo->findAddress($address_id);
        abort_if(!$address, 404);
        $this->authorize('update', $address);
        $validated = $request->validated();
        $this->service->updateAddress($address, $request->validate());
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address updated successfully!');
    }
    /**
     * Remove the specified address from storage.
     */
    public function destroy($address_id, $user_id)
    {

        $this->service->destroyAddress($address_id, $user_id);
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default for its type.
     */
    public function setDefault($address_id, $user_id)
    {
       $this->service->setDefault($address_id, $user_id);
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Default address updated successfully!');
    }
    public function edit($address_id,$user_id)
    {
         $user = Auth::user();
          // Verify the address belongs to the authenticated user
        if ( intval($user_id) !== $user->id) {
            abort(403);
        }else{
            $district =  $this->repo->getDistricts();
            $address = $this->repo->findAddress($address_id);
            if (!$address) {
                abort(404);
            }
        }

        return view('backend_panel_view_customer.pages.create_edit_address', compact('address','district'));
    }
}
