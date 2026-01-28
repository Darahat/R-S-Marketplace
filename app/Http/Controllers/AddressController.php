<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserAddressRepository;
use App\Http\Requests\UserAddressRequests;
use App\Services\AddressService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Address;
class AddressController extends Controller
{
    use AuthorizesRequests;

    public function __construct( private UserAddressRepository $repo, private AddressService $service){}

    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        $userId = Auth::id();
        $shippingAddresses =$this->repo->getUserShippingAddress($userId);
        $billingAddresses = $this->repo->getUserBillingAddress($userId);
        return view('backend_panel_view_customer.pages.address_list', compact('shippingAddresses', 'billingAddresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        $this->authorize('create', Address::class);
        $district =  $this->repo->getDistricts();
        return view('backend_panel_view_customer.pages.create_edit_address', ['district' => $district]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(UserAddressRequests $request)
    {
        $this->authorize('create', Address::class);
        $validated = $request->validated();
        $this->service->createAddress(Auth::id(),$validated);
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address added successfully!');
    }

   public function edit($address_id,$user_id){
        $address = $this->repo->findAddress($address_id);
        abort_if(!$address,404,'Address Not found');
        $district = $this->repo->getDistricts();
        return view('backend_panel_view_customer.pages.create_edit_address', compact('address', 'district'));

    }

    public function update(UserAddressRequests $request, $address_id, $user_id)
    {
        $address = $this->repo->findAddress($address_id);
        abort_if(!$address, 404);
        $this->authorize('update', $address);
        $validated = $request->validated();
        $success = $this->service->updateAddress($address_id, $user_id, $validated);
         if (!$success) {
            return redirect()->back()
                ->with('error', 'Failed to update address.')
                ->withInput();
        }
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address updated successfully!');
    }
    /**
     * Remove the specified address from storage.
     */
    public function destroy($address_id, $user_id)
    {
        $address = $this->repo->findAddress($address_id);
        $this->authorize('delete', $address);
        abort_if(!$address, 404, 'Address not found');
       $success = $this->service->destroyAddress($address_id, Auth::id());
        if (!$success) {
            return redirect()->back()
                ->with('error', 'Failed to delete address.');
        }
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default for its type.
     */
    public function setDefault($address_id, $user_id)
    { $address = $this->repo->findAddress($address_id);

        abort_if(!$address, 404, 'Address not found');

        $this->authorize('update', $address);

       $success = $this->service->toggleDefault($address_id, $user_id);
        if (!$success) {
            return redirect()->back()
                ->with('error', 'Failed to update default address.');
        }
        return redirect()->route('customer.addresses.index')
            ->with('success', 'Default address updated successfully!');
    }

}
