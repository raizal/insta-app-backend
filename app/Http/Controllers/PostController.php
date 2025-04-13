<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Comment;

class PostController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        
        $posts = Post::with(['user:id,name,email,username,profile_picture'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Store a newly created post with image and caption.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:1000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('posts', $imageName, 'public');
            
            $post = Post::create([
                'user_id' => Auth::id(),
                'caption' => $request->caption,
                'image_path' => $imagePath
            ]);

            // Add the image URL to the response, but don't store it in the database
            $post->image_url = url('/img/post/' . basename($imagePath));

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image upload failed'
        ], 500);
    }

    /**
     * Display the specified post with all comments.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $post = Post::with([
            'user:id,name,email,username,profile_picture',
        ])->withCount(['likes', 'comments'])->with('user:id,name,email,username,profile_picture')->find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Update the post caption.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Check if current user is the owner of the post
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $post->caption = $request->caption;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => 'Post caption updated successfully',
            'data' => $post
        ]);
    }

    /**
     * Remove the specified post.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Check if current user is the owner of the post
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Delete the image from storage
        if (Storage::disk('public')->exists($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * Toggle like on a post.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function toggleLike(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Check if the user has already liked this post
        $existingLike = \App\Models\Like::where('user_id', Auth::id())
            ->where('post_id', $id)
            ->first();


        if ($existingLike) {
            // Unlike the post
            $existingLike->delete();
        } else {
            // Like the post
            \App\Models\Like::create([
                'user_id' => Auth::id(),
                'post_id' => $id,
            ]);
        }

        // Get the updated like count for the post
        $likeCount = \App\Models\Like::where('post_id', $id)->count();
        return response()->json([
            'success' => true,
            'message' => 'Post unliked successfully',
            'liked' => !$existingLike,
            'likeCount' => $likeCount
        ]);
    }

    /**
     * Get posts created by the authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyPosts(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        
        $posts = Post::with(['user:id,name,email,username,profile_picture'])
            ->withCount(['likes'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($perPage);
            
        // Add a "liked" attribute to each post
        $posts->getCollection()->transform(function ($post) {
            $post->liked = $post->likes()->where('user_id', Auth::id())->exists();
            return $post;
        });
        
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
}
