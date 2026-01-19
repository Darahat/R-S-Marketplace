<?php
namespace App\Repositories;

use App\Models\Address;
class UserAddressRepository{

    public function UserShippingAddress(){
        return Address::with('districts','upazilas','unions');
    }
    public function UserBillingAddress(){
        return Address::with('districts','upazilas','unions');
    }
}
