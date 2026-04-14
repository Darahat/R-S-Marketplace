<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CustomerPasswordRecoveryService
{
    public function __construct(private AuthRepository $authRepository)
    {
    }

    public function sendResetLink(string $email): string
    {
        $customer = $this->authRepository->findCustomerByEmail($email);

        // Keep response consistent to avoid account enumeration.
        if (!$customer) {
            return Password::RESET_LINK_SENT;
        }

        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $credentials): string
    {
        $customer = $this->authRepository->findCustomerByEmail($credentials['email']);
        if (!$customer) {
            return Password::INVALID_USER;
        }

        return Password::reset(
            $credentials,
            function ($user, $password) {
                $this->authRepository->updatePassword($user, $password, Str::random(60));
                event(new PasswordReset($user));
            }
        );
    }
}
