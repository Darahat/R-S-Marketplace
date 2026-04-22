<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class CustomerProfileService
{
    public function __construct(private CustomerProfileRepository $repo, private AvifImageService $imageService)
    {
    }

    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    public function updateProfilePhoto(User $user, UploadedFile $file): string
    {
        $user->profile_photo = $this->imageService->savePublicImage($file, 'profile_photos', 'profile_' . $user->id, $user->profile_photo);
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
