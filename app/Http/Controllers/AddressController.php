<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\District;

class AddressController extends Controller
{
    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        $user = Auth::user();

         $shippingAddresses = DB::table('addresses')
        ->leftJoin('districts', 'addresses.district_id', '=', 'districts.id')
        ->leftJoin('upazilas', 'addresses.upazila_id', '=', 'upazilas.id')
        ->leftJoin('unions', 'addresses.union_id', '=', 'unions.id')
        ->where('addresses.address_type', 'shipping')
        ->where('addresses.user_id', $user->id)
        ->orderBy('addresses.is_default', 'desc')
        ->select(
            'addresses.*',
            'districts.name as district_name',
            'upazilas.name as upazila_name',
            'unions.name as union_name'
        )
        ->get();

    $billingAddresses = DB::table('addresses')
        ->leftJoin('districts', 'addresses.district_id', '=', 'districts.id')
        ->leftJoin('upazilas', 'addresses.upazila_id', '=', 'upazilas.id')
        ->leftJoin('unions', 'addresses.union_id', '=', 'unions.id')
        ->where('addresses.address_type', 'billing')
        ->where('addresses.user_id', $user->id)
        ->orderBy('addresses.is_default', 'desc')
        ->select(
            'addresses.*',
            'districts.name as district_name',
            'upazilas.name as upazila_name',
            'unions.name as union_name'
        )
        ->get();

        return view('backend_panel_view_customer.pages.address_list', compact('shippingAddresses', 'billingAddresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        $districts = District::with(['upazilas' => function ($q) {
            $q->orderBy('name');
        }, 'upazilas.unions' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('name')->get();
        return view('backend_panel_view_customer.pages.create_edit_address', compact('districts'));
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // return $request->all();
        $validated = $request->validate([
            'address_type' => 'required|in:shipping,billing',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street_address' => 'required|string|max:255',
            'district_id' => 'required',
            'upazila_id' => 'required',
            'union_id' => 'required',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, remove default status from other addresses of same type
        if ($request->has('is_default') && $request->is_default) {
            DB::table('addresses')
    ->where('user_id', $user->id)
                ->where('address_type', $validated['address_type'])
                ->update(['is_default' => false]);
        }

        $validated['user_id'] = $user->id;
        $address = DB::table('addresses')->insert($validated);

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Address added successfully!');
    }

    /**
     * Show the form for editing the specified address.
     */


    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, $address_id, $user_id)
    {
        $user = Auth::user();

        if ((int) $user_id !== $user->id) {
            abort(403);
        }

        $address = DB::table('addresses')->where('id', $address_id)->first();
        if (!$address) {
            abort(404);
        }

        $validated = $request->validate([
            'address_type' => 'required|in:shipping,billing',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street_address' => 'required|string|max:255',
            'district_id' => 'required',
            'upazila_id' => 'required',
            'union_id' => 'required',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'required|in:1,0',
        ]);

        // Cast values
        $newType = $validated['address_type'];
        $isDefault = (bool) $validated['is_default'];

        // If setting this address as default
        if ($isDefault) {
            // Clear other defaults for this user & type
            DB::table('addresses')
                ->where('user_id', $user->id)
                ->where('address_type', $newType)
                ->where('id', '!=', $address_id)
                ->update(['is_default' => false]);
        }

        // If this address is no longer default, and it was default before
        if (!$isDefault && $address->is_default) {
            // Find another address to promote as default
            $newDefault = DB::table('addresses')
                ->where('user_id', $user->id)
                ->where('address_type', $address->address_type)
                ->where('id', '!=', $address_id)
                ->first();

            if ($newDefault) {
                DB::table('addresses')
                    ->where('id', $newDefault->id)
                    ->update(['is_default' => true]);
            } else {
                // Prevent removal of default if no fallback exists
                return redirect()->back()->with('error', 'Cannot unset default. No other address available to promote.');
            }
        }

        // Perform update
        $success = DB::table('addresses')->where('id', $address_id)->update($validated);

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
            $newDefault = DB::table('addresses')
                ->where('user_id', $user->id)
                ->where('address_type', $address->address_type)
                ->where('id', '!=', $address->id)
                ->first();

            if ($newDefault) {
                DB::table('addresses')
                    ->where('id', $newDefault->id)
                    ->update(['is_default' => true]);
            }
        }

        DB::table('addresses')->where('id', $address_id)->delete();

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
            $districts = District::with(['upazilas' => function ($q) {
                $q->orderBy('name');
            }, 'upazilas.unions' => function ($q) {
                $q->orderBy('name');
            }])->orderBy('name')->get();
            $address = DB::table('addresses')->where('id', $address_id)->first();
            if (!$address) {
                abort(404);
            }
        }

        return view('backend_panel_view_customer.pages.create_edit_address', compact('address','districts'));
    }
}