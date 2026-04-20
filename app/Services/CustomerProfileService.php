<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CustomerProfileService
{
    public function __construct(private CustomerProfileRepository $repo)
    {
    }

    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    public function updateProfilePhoto(User $user, UploadedFile $file): string
    {
        $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('profile_photos');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);

        if (!empty($user->profile_photo)) {
            $oldPath = public_path($user->profile_photo);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->profile_photo = 'profile_photos/' . $fileName;
        $this->repo->save($user);

        return asset($user->profile_photo);
    }

    public function updateField(User $user, string $field, mixed $value): void
    {
        $user->{$field} = $value;
        $this->repo->save($user);
    }

    public function getPhotoUrl(User $user): string
    {
        $photoPath = $user->profile_photo ?: 'images/default-avatar.png';

        return asset($photoPath);
    }
}
