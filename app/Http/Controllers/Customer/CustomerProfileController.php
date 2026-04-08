<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $field = $request->input('field');

        if ($field === 'profile_photo') {
            $validator = Validator::make($request->all(), [
                'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $file = $request->file('profile_photo');
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
            $user->save();

            return response()->json([
                'message' => 'Profile photo updated successfully.',
                'photo_url' => asset($user->profile_photo),
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

        $user->{$field} = $request->input('value');
        $user->save();

        return response()->json([
            'message' => ucfirst($field) . ' updated successfully.',
        ]);
    }

    public function instantPhotoView()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $photoPath = $user->profile_photo ?: 'images/default-avatar.png';

        return response()->json([
            'photo_url' => asset($photoPath),
        ]);
    }
}
