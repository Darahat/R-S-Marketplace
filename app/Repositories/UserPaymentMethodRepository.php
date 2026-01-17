<?php
namespace App\Repositories;
use App\Models\UserPaymentMethod;

class UserPaymentMethodRepository
{
    public function listForUser(int $userId){
        $paymentMethods = UserPaymentMethod::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return $paymentMethods;
    }
    public function findForUser(int $userId , int $id):UserPaymentMethod{
        $paymentMethod = UserPaymentMethod::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
        return  $paymentMethod;
    }
    public function clearDefault(int $userId):void{
        UserPaymentMethod::where('user_id', $userId)->update(['is_default' => false]);
    }
}
