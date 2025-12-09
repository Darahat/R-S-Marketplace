<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CustomerProfileApiController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $field = $request->input('field');

        if (!in_array($field, ['name', 'email', 'mobile', 'profile_photo'])) {
            return response()->json(['message' => 'Invalid field'], 400);
        }

        if ($field === 'profile_photo') {
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');

                // Generate a unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Move the file to the public/profile_photos directory
                $file->move(public_path('profile_photos'), $filename);

                // Save the relative path or filename to DB
                $user->profile_photo = 'profile_photos/' . $filename;
            }
        } else {
            $user->$field = $request->input('value');
        }

        $user->save();

        return response()->json(['message' => ucfirst($field).' updated successfully']);
    }
    public function instant_photo_view() {
        $user = Auth::user();
        return response()->json([
            'photo_url' => $user->profile_photo 
                ? asset('storage/' . $user->profile_photo) 
                : asset('images/default-avatar.png'),
        ]);
    }
    
   
    
     
 
}