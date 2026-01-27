<?php
namespace App\Services;

use App\Repositories\UserAddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\Address;
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

    if($newIsDefault && !$oldIsDefault){
        $this->repo->unsetOtherDefaults($userId,$address->addressType,$addressId,false);
    }elseif(!$newIsDefault && $oldIsDefault){
        $fallbackAddress =$this->findFallbackAddress($userId, $address->address_type, $addressId);
        if($fallbackAddress){
            $this->repo->updateAddress($fallbackAddress->id, ['is_default' => true]);
        }
    }
    return $this->repo->updateAddress($addressId,$data);
    }
$this->handleDefaultChange(
        $userId,
        $data['address_type'],
        0,
        true
    );
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


    private function findFallbackAddress(int $userId, String $address_type, int $excludeId): ?Address{
        // Get all addresses of same type except the one we're excluding
        $addresses = $address_type === 'shipping' ? $this->repo->getUserBillingAddress($userId) : $this->repo->getUserShippingAddress($userId);
        return $addresses->where('id', '!=', $excludeId)->first();
    }

}
