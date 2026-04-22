<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProfileController extends Controller
{
    public function __construct(private CustomerProfileService $service)
    {
    }

    public function update(Request $request)
    {
        $user = $this->service->getAuthenticatedUser();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $field = $request->input('field');

        if ($field === 'profile_photo') {
            $validator = Validator::make($request->all(), [
                'profile_photo' => 'required|file|mimes:jpg,jpeg,png,gif,webp,bmp,avif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $file = $request->file('profile_photo');
            $photoUrl = $this->service->updateProfilePhoto($user, $file);

            return response()->json([
                'message' => 'Profile photo updated successfully.',
                'photo_url' => $photoUrl,
            ]);
        }

        $rules = match ($field) {
            'name' => ['value' => 'required|string|max:255'],
            'email' => ['value' => 'required|email|max:255|unique:users,email,' . $user->id],
            'mobile' => ['value' => 'nullable|string|max:20'],
            default => null,
        };

        if ($rules === null) {
            return response()->json([
                'errors' => ['field' => ['Invalid profile field provided.']]
            ], 422);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->service->updateField($user, $field, $request->input('value'));

        return response()->json([
            'message' => ucfirst($field) . ' updated successfully.',
        ]);
    }

    public function instantPhotoView()
    {
        $user = $this->service->getAuthenticatedUser();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'photo_url' => $this->service->getPhotoUrl($user),
        ]);
    }
}
