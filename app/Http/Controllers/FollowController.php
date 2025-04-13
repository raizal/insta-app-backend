<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Follow a user.
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function follow(Request $request, string $username): JsonResponse
    {
        // Find user by username
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Can't follow yourself
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 422);
        }

        // Check if already following
        if (Auth::user()->isFollowing($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already following this user'
            ], 422);
        }

        // Create follow relationship
        Auth::user()->following()->attach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'You are now following ' . $user->username,
            'data' => [
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count
            ]
        ]);
    }

    /**
     * Unfollow a user.
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function unfollow(Request $request, string $username): JsonResponse
    {
        // Find user by username
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Check if not following
        if (!Auth::user()->isFollowing($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not following this user'
            ], 422);
        }

        // Remove follow relationship
        Auth::user()->following()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'You have unfollowed ' . $user->username,
            'data' => [
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count
            ]
        ]);
    }

    /**
     * Get followers and following of a user.
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function getFollowersAndFollowing(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        
        $perPage = $request->query('per_page', 15);
        $type = $request->query('type', 'followers'); // 'followers' or 'following'
        
        if ($type === 'followers') {
            $users = $user->followers()
                ->select(['users.id', 'users.name', 'users.username', 'users.profile_picture'])
                ->paginate($perPage);
        } else {
            $users = $user->following()
                ->select(['users.id', 'users.name', 'users.username', 'users.profile_picture'])
                ->paginate($perPage);
        }
        
        // Add is_following and is_followed_by attributes
        if (Auth::check()) {
            $users->getCollection()->transform(function ($user) {
                $user->is_following = Auth::user()->isFollowing($user);
                $user->is_followed_by = Auth::user()->isFollowedBy($user);
                return $user;
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Check if the authenticated user follows the given user.
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function checkFollowStatus(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is_following' => false,
                    'is_followed_by' => false
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => Auth::user()->isFollowing($user),
                'is_followed_by' => Auth::user()->isFollowedBy($user)
            ]
        ]);
    }

    /**
     * Toggle follow status for a user (follow if not following, unfollow if following).
     *
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function toggleFollow(Request $request, string $username): JsonResponse
    {
        // Find user by username
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Can't follow yourself
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 422);
        }

        // Check if already following
        $isFollowing = Auth::user()->isFollowing($user);
        
        if ($isFollowing) {
            // Unfollow
            Auth::user()->following()->detach($user->id);
            $message = 'You have unfollowed ' . $user->username;
            $followed = false;
        } else {
            // Follow
            Auth::user()->following()->attach($user->id);
            $message = 'You are now following ' . $user->username;
            $followed = true;
        }

        // Refresh user model to get updated counts
        $user->refresh();
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_following' => $followed,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count
            ]
        ]);
    }
}
