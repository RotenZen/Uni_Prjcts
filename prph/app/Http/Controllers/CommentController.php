<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    /**
     * Display all comments for a post (public)
     * GET /api/posts/{post_id}/comments
     */
    public function index($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = Comment::with('user:u_name,user_id')
            ->where('post_id', $post_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Add a comment (authenticated user)
     * POST /api/posts/{post_id}/comments
     */
    public function store(Request $request, $post_id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comment = Comment::create([
            'post_id' => $post_id,
            'user_id' => $request->user()->user_id,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user:u_name,user_id')
        ], 201);
    }

    /**
     * Delete comment (only owner or admin)
     * DELETE /api/comments/{comment_id}
     */
    public function destroy(Request $request, $comment_id)
    {
        $comment = Comment::find($comment_id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user = $request->user();

        if ($comment->user_id !== $user->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
