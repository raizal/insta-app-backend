<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get a user's profile by username.
     *
     * @param string $username
     * @return JsonResponse
     */
    public function getUserProfile(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->select(['id', 'name', 'username', 'email', 'profile_picture', 'created_at'])
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update only the fields that were provided in the request
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('username')) {
            $user->username = $request->username;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Update the authenticated user's password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => ['current_password' => ['The provided password does not match our records.']]
            ], 422);
        }
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Upload a profile picture for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadProfilePicture(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        
        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('profile', $imageName, 'public');
            
            $user->profile_picture = $imagePath;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'data' => [
                    'profile_picture' => $user->profile_picture,
                    'profile_picture_url' => $user->profile_picture_url
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Profile picture upload failed'
        ], 500);
    }

    /**
     * Remove the authenticated user's profile picture.
     *
     * @return JsonResponse
     */
    public function removeProfilePicture(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->profile_picture) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            
            // Clear the profile_picture field
            $user->profile_picture = null;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No profile picture to remove'
        ], 400);
    }
}
