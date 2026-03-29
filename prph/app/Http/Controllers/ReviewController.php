<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Post;
use App\Models\Resource;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Create or update a review.
     * If resource link is new, it creates the resource first.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'target_type' => 'required|in:post,resource',
            'target_id' => 'required_if:target_type,post|integer|nullable',
            'resource_link' => 'required_if:target_type,resource|url|nullable',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = $request->user()->user_id;
        $targetId = null;

        if ($data['target_type'] === 'resource') {
            $url = $data['resource_link'];
            $domain = parse_url($url, PHP_URL_HOST);

            // ✅ Check against blacklist
            $isBlacklisted = \DB::table('blacklisted_domains')
                ->where('domain', $domain)
                ->exists();

            if ($isBlacklisted) {
                return response()->json([
                    'message' => 'This resource domain is blacklisted. Submission denied.'
                ], 403);
            }

            // ✅ Create or get resource
            $resource = \App\Models\Resource::firstOrCreate(
                ['url' => $url],
                [
                    'title' => $url,
                    'type' => 'other',
                    'domain' => $domain,
                ]
            );

            $targetId = $resource->resource_id;
        } else {
            // ✅ Handle post reviews
            $post = \App\Models\Post::find($data['target_id']);
            if (!$post) {
                return response()->json(['message' => 'Post not found.'], 404);
            }
            $targetId = $post->post_id;
        }

        // ✅ Create or update review
        $review = \App\Models\Review::updateOrCreate(
            [
                'user_id' => $userId,
                'target_type' => $data['target_type'],
                'target_id' => $targetId,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'created_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $review,
        ], 201);
    }

    /**
     * Get all reviews for a given post or resource
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'target_type' => 'required|in:post,resource',
            'target_id' => 'required|integer',
        ]);

        $reviews = Review::where('target_type', $data['target_type'])
            ->where('target_id', $data['target_id'])
            ->with('user:u_name,user_id')
            ->get();

        return response()->json(['reviews' => $reviews]);
    }

    /**
     * Delete a user's review (user can delete only their own)
     */
    public function destroy(Request $request, $reviewId)
    {
        $userId = $request->user()->user_id;
        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        if ($review->user_id !== $userId && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to delete this review.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}
