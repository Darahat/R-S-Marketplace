<?php
namespace App\Repositories;

use App\Models\Address;
use App\Models\District;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserAddressRepository{
    /**
     * Get all shipping addresses for a user.
     */
    public function getUserShippingAddress(int $userId,array $columns = ['*']): Collection {
        return Address::with(['district','upazila','union'])
        ->where('address_type','shipping')
        ->where('user_id',$userId)
        ->orderByDesc('is_default')
        ->get($columns);

    }
    /**
     * Get all billing addresses for a user.
     */
    public function getUserBillingAddress(int $userId,array $columns = ['*']): Collection{
        return Address::with(['district','upazila','union'])
        ->where('address_type','billing')
        ->where('user_id',$userId)
        ->orderByDesc('is_default')
        ->get($columns);

    }

    public function getAllUsersAddress(int $perPage = 15): LengthAwarePaginator{
    return Address::with(['user', 'district', 'upazila', 'union'])
        ->orderByDesc('id')
        ->paginate($perPage);
    }
    /**
     * Get all Districts, upzilla, unions.
     */
    public function getDistricts(): Collection{
    return District::with(['upazila' => fn ($q) =>
            $q->orderBy('name'),
         'upazila.union' => fn ($q) =>$q->orderBy('name')])
         ->orderBy('name')
         ->get();

    }
    /**
     * Manage Default.
     */
    public function unsetOtherDefaults(int $userId,string $addressType,int $address_id,bool $defaultValue): bool
    {
    return Address::where('user_id', $userId)
                ->where('address_type', $addressType)
                ->where('id', '!=', $address_id)
                ->update(['is_default' => $defaultValue]);
    }
    /**
     * Store a new address.
     */
    public function createAddress(array $validatedData): ?Address{
    return Address::create($validatedData);
    }
    /**
     * Update an Address.
     */
    public function updateAddress(int $address_id,array $data): bool{
    return Address::where('id', $address_id)->update($data) > 0;
    }
    /**
     * Get Address By id.
     */
    public function findAddress(int $address_id): ? Address{
    return Address::find($address_id);
    }
    /**
     * Delete Address by ID.
     */
    public function deleteAddress(int $address_id): bool{
    return Address::where('id',$address_id)->delete() > 0;
    }


}

