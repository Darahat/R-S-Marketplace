<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Repositories\UserAddressRepository;
use App\Http\Requests\UserAddressRequests;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\throwException;

class AddressController extends Controller
{

    public function __construct( private UserAddressRepository $repo){}

    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        $user = Auth::user();
        $shippingAddresses =$this->repo->getUserShippingAddress($user->id,['addresses.*',
             'districts.name as district_name',
             'upazilas.name as upazila_name',
             'unions.name as union_name']);
        $billingAddresses = $this->repo->getUserBillingAddress($user->id,['addresses.*',
             'districts.name as district_name',
             'upazilas.name as upazila_name',
             'unions.name as union_name']);
        return view('backend_panel_view_customer.pages.address_list', compact('shippingAddresses', 'billingAddresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        $districts = $this->repo->getDistricts();
        return view('backend_panel_view_customer.pages.create_edit_address', compact('districts'));
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
        $user = Auth::user();

        if ((int) $user_id !== $user->id) {
            abort(403);
        }

        $address = $this->repo->findAddress($address_id);
        if (!$address) {
            abort(404);
        }

        $validated = $request->validated();

        // Cast values
        $newType = $validated['address_type'];
        $isDefault = (bool) $validated['is_default'];

        // If setting this address as default
        if ($isDefault) {
            // Clear other defaults for this user & type

            $this->repo->updateAddress($address_id,$validated);
        }

        // If this address is no longer default, and it was default before
        if (!$isDefault && $address->is_default) {
            // Find another address to promote as default
            $newDefault = $this->repo->findAddress($address_id);

            if ($newDefault) {
            $this->repo->unsetOtherDefaults($user->id,$newType,$address_id,true);
            } else {
                // Prevent removal of default if no fallback exists
                return redirect()->back()->with('error', 'Cannot unset default. No other address available to promote.');
            }
        }

        // Perform update
        $success = $this->repo->updateAddress($address_id,$validated);

        if (!$success) {
            return redirect()->back()->with('error', 'Failed to update address.');
        }

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address updated successfully!');
    }



    /**
     * Remove the specified address from storage.
     */
    public function destroy($address_id, $user_id)
    {
        $user = Auth::user();

        // Verify the address belongs to the authenticated user
        if (intval($user_id) !== Auth::id()) {
            abort(403);
        }

        // Fetch the address
        $address = DB::table('addresses')->where('id', $address_id)->first();

        if (!$address) {
            return redirect()->route('customer.addresses.index')
                ->with('error', 'Address not found!');
        }

        // If this was the default address, set another one as default
        if ($address->is_default) {
            $newDefault = $this->repo->findAddress($address_id);
$data = ['is_default' => true];
            if ($newDefault) {
            $this->repo->updateAddress($address_id,$data);

            }
        }

         try {
            $this->repo->deleteAddress($address_id);
         } catch (\Throwable $th) {
            throw new \Exception("Error Processing Request", 0, $th);

         }

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default for its type.
     */
    public function setDefault($address_id, $user_id)
    {
        $user = Auth::user();


        // Verify the address belongs to the authenticated user
        if (intval($user_id) !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $address = DB::table('addresses')->where('id', $address_id)->first();

        if (!$address) {
            return redirect()->route('customer.addresses.index')
                ->with('error', 'Address not found!');
        }

        // If clicking on already default address, unset it
        if ($address->is_default) {
            DB::table('addresses')
                ->where('id', $address_id)
                ->update(['is_default' => false]);

            return redirect()->route('customer.addresses.index')
                ->with('success', 'Address removed as default successfully!');
        }

        // For new default address - first remove default from others of same type
        DB::table('addresses')
            ->where('user_id', $user->id)
            ->where('address_type', $address->address_type)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Then set the new address as default
        $data =
        DB::table('addresses')
            ->where('id', $address_id)
            ->update(['is_default' => true]);

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
            $districts =  $this->repo->getDistricts();
            $address = $this->repo->findAddress($address_id);
            if (!$address) {
                abort(404);
            }
        }

        return view('backend_panel_view_customer.pages.create_edit_address', compact('address','districts'));
    }
}
