<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Add a comment to a post.
     * 
     * @param Request $request
     * @param string $id Post ID
     * @return JsonResponse
     */
    public function addComment(Request $request, string $id): JsonResponse
    {
        // Validate the post exists
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the comment
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $id,
            'body' => $request->body,
            'parent_id' => $request->parent_id
        ]);

        // Load relationships
        $comment->load('user:id,name,email,username,profile_picture');
        
        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => $comment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $comment = Comment::find($id);
        
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found'
            ], 404);
        }

        // Check if current user is the owner of the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Get paginated comments for a post.
     * 
     * @param Request $request
     * @param string $id Post ID
     * @return JsonResponse
     */
    public function getPostComments(Request $request, string $id): JsonResponse
    {
        // Validate the post exists
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        $perPage = $request->query('per_page', 5);
        
        // Get comments for this post, excluding replies (they'll be nested)
        // Include replies count using withCount
        $comments = Comment::with(['user:id,name,email,username,profile_picture', 'replies.user:id,name,email,username,profile_picture'])
            ->withCount('replies')
            ->where('post_id', $id)
            ->whereNull('parent_id')
            ->latest()
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }
}
