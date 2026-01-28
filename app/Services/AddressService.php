<?php
namespace App\Services;

use App\Repositories\UserAddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Address;
class AddressService{
      use AuthorizesRequests;
    public function __construct(private UserAddressRepository $repo)
    {
    }

    public function createAddress(int $userId, array $data): ?Address{
        if($data['is_default'] ?? false){
            $this->handleDefaultChange(
        $userId,
        $data['address_type'],
        0,
        true
    );
        }
        return $this->repo->createAddress($data);
    }
    public function updateAddress(int $addressId,int $userId,array $data): bool {

    $address = $this->repo->findAddress($addressId);
    if(!$address){
        return false;
    }
    $newIsDefault = $data['is_default'] ?? false;
    $oldIsDefault = $address->is_default;

    if ($newIsDefault !== $oldIsDefault) {
    $this->handleDefaultChange(
        $userId,
        $address->address_type,
        $addressId,
        $newIsDefault
    );
}

    return $this->repo->updateAddress($addressId,$data);
    }

    public function destroyAddress($address_id, $user_id){
         $user = Auth::user();

        // Verify the address belongs to the authenticated user
        if (intval($user_id) !== Auth::id()) {
            abort(403);
        }

        // Fetch the address
        $address = $this->repo->findAddress($address_id);

        if (!$address) {
            return redirect()->route('customer.addresses.index')
                ->with('error', 'Address not found!');
        }

        // If this was the default address, set another one as default
        if ($address->is_default) {
               $this->handleDefaultChange(
        $user_id,
        $address->address_type,
        $address_id,
        false
    );
        }

         try {
            $this->repo->deleteAddress($address_id);
         } catch (\Throwable $th) {
            throw new \Exception("Error Processing Request", 0, $th);

         }
    }
    public function handleDefaultChange(int $userId,string $address_type,int $currentAddressId, bool $makeDefault): void{
         if($makeDefault){
            // Set this as default -> unset others
            $this->repo->unsetOtherDefaults(
                $userId, $address_type, $currentAddressId, false
            );
         }
        else{
            $fallback = $this->findFallbackAddress($userId, $address_type, $currentAddressId);
            if($fallback){
                $this->repo->updateAddress($fallback->id,['is_default' => true]);
            }
        }
    }
    public function toggleDefault(int $address_id, int $user_id):bool{
 $address = $this->repo->findAddress($address_id);
    if(!$address){
        return false;
    }
     if ($address->is_default) {
           $this->handleDefaultChange(
        $user_id,
        $address->address_type,
        $address_id,
        false
    );
     }
     return $this->repo->updateAddress($address_id,['is_default'=> !$address->is_default]);
    }

    private function findFallbackAddress(int $userId, String $address_type, int $excludeId): ?Address{
        // Get all addresses of same type except the one we're excluding
        $addresses = $address_type === 'shipping' ? $this->repo->getUserBillingAddress($userId) : $this->repo->getUserShippingAddress($userId);
        return $addresses->where('id', '!=', $excludeId)->first();
    }

}
