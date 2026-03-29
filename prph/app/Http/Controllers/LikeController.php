<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LikeDislike;
use App\Models\Post;


//Add admin features to get user id's who reacted
class LikeController extends Controller
{
    /**
     * Toggle like or dislike for a post
     * POST /api/posts/{post_id}/react
     */
    public function react(Request $request, $post_id)
    {
        $request->validate([
            'type' => 'required|in:like,dislike'
        ]);

        $user = $request->user();

        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Check if user already reacted
        $existing = LikeDislike::where('user_id', $user->user_id)
            ->where('post_id', $post_id)
            ->first();

        if ($existing) {
            // remove reaction by pressing twice
            if ($existing->type === $request->type) {
                $existing->delete();
                return response()->json(['message' => 'Reaction removed']);
            } else {
                // change reaction
                $existing->update(['type' => $request->type]);
                return response()->json(['message' => 'Reaction updated']);
            }
        }

        // like/dislike for the first time on a post
        LikeDislike::create([
            'user_id' => $user->user_id,
            'post_id' => $post_id,
            'type' => $request->type,
        ]);

        return response()->json(['message' => 'Reaction added']);
    }

    /**
     * Get total like/dislike counts for a post
     * GET /api/posts/{post_id}/reactions
     */
    public function count($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $likes = LikeDislike::where('post_id', $post_id)->where('type', 'like')->count();
        $dislikes = LikeDislike::where('post_id', $post_id)->where('type', 'dislike')->count();

        return response()->json([
            'post_id' => $post_id,
            'likes' => $likes,
            'dislikes' => $dislikes
        ]);
    }
}
