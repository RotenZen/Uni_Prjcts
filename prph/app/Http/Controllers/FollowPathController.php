<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\FollowPath;
use App\Models\PathProgress;
use App\Models\Resource;

class FollowPathController extends Controller
{
    /**
     * POST /api/posts/{post_id}/follow
     * Follow a path (post). Idempotent thanks to DB unique constraint.
     */
    public function follow(Request $request, $post_id)
    {
        $user = $request->user();

        $post = Post::with('resources')->find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Create (or retrieve existing) follow row
        $follow = FollowPath::firstOrCreate(
            ['user_id' => $user->user_id, 'post_id' => $post->post_id],
            ['started_at' => now()]
        );

        // Optionally pre-seed progress rows (one per resource). Safe via upsert.
        if ($post->resources && $post->resources->count()) {
            $rows = [];
            foreach ($post->resources as $res) {
                $rows[] = [
                    'follow_id' => $follow->follow_id,
                    'resource_id' => $res->resource_id,
                    'is_completed' => false,
                    'created_at' => now(),
                ];
            }
            // Upsert by unique (follow_id, resource_id)
            DB::table('path_progress')->upsert(
                $rows,
                ['follow_id', 'resource_id'],
                ['is_completed', 'created_at']
            );
        }

        return response()->json([
            'message' => 'Path followed',
            'follow' => $follow,
        ], 201);
    }

    /**
     * DELETE /api/posts/{post_id}/follow
     * Unfollow a path (also removes progress rows).
     */
    public function unfollow(Request $request, $post_id)
    {
        $user = $request->user();
        $follow = FollowPath::where('user_id', $user->user_id)
            ->where('post_id', $post_id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Nothing to unfollow'], 200);
        }

        // Cascade progress delete
        PathProgress::where('follow_id', $follow->follow_id)->delete();
        $follow->delete();

        return response()->json(['message' => 'Unfollowed']);
    }

    /**
     * GET /api/me/follows
     * List my followed posts with completion percentage.
     */
    public function myFollows(Request $request)
    {
        $user = $request->user();

        // load followed posts + basic progress stats
        $follows = FollowPath::with(['post.resources'])
            ->where('user_id', $user->user_id)
            ->get();

        $followIds = $follows->pluck('follow_id');

        $completedCounts = PathProgress::select('follow_id', DB::raw('COUNT(*) as completed'))
            ->whereIn('follow_id', $followIds)
            ->where('is_completed', true)
            ->groupBy('follow_id')
            ->pluck('completed', 'follow_id');

        $data = $follows->map(function ($f) use ($completedCounts) {
            $total = $f->post?->resources?->count() ?? 0;
            $done = $completedCounts[$f->follow_id] ?? 0;
            if ($total > 0) {
                $pct = round(($done * 100) / $total, 2);
            } else {
                $pct = 0;
            }


            return [
                'follow_id' => $f->follow_id,
                'post_id'   => $f->post_id,
                'started_at'=> $f->started_at,
                'title'     => $f->post?->title,
                'skill'     => $f->post?->skill?->s_name ?? null,
                'steps_total' => $total,
                'steps_done'  => (int)$done,
                'percent'     => $pct,
            ];
        });

        return response()->json($data);
    }

    /**
     * GET /api/me/follows/{post_id}/progress
     * Return step-by-step progress for a followed path.
     */
    public function showProgress(Request $request, $post_id)
    {
        $user = $request->user();

        $follow = FollowPath::where('user_id', $user->user_id)
            ->where('post_id', $post_id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Not following this path'], 404);
        }

        // get ordered resources for the post
        $post = Post::with('resources')->findOrFail($post_id);

        // map progress by resource_id
        $progressMap = PathProgress::where('follow_id', $follow->follow_id)
            ->get()
            ->keyBy('resource_id');

        $steps = $post->resources->map(function ($res) use ($progressMap) {
            $p = $progressMap->get($res->resource_id);
            return [
                'resource_id' => $res->resource_id,
                'title'       => $res->title ?? $res->name ?? $res->url, // depends on your Resource fields
                'url'         => $res->url ?? null,
                'is_completed'=> (bool)optional($p)->is_completed,
                'completed_at'=> optional($p)->completed_at,
                'time_to_completion' => optional($p)->time_to_completion,
                'order'       => $res->pivot?->order_number,
            ];
        });

        return response()->json([
            'follow_id' => $follow->follow_id,
            'post_id'   => $post_id,
            'steps'     => $steps,
        ]);
    }

    /**
     * POST /api/me/follows/{post_id}/progress
     * Body: { "resource_id": 123, "is_completed": true, "time_to_completion": 45 }
     * Toggle / set completion on a single resource for the current user.
     */
    public function setProgress(Request $request, $post_id)
    {
        $user = $request->user();

        $request->validate([
            'resource_id' => 'required|integer',
            'is_completed'=> 'required|boolean',
            'time_to_completion' => 'nullable|integer',
        ]);

        $follow = FollowPath::where('user_id', $user->user_id)
            ->where('post_id', $post_id)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Not following this path'], 404);
        }

        // Ensure the resource actually belongs to this post
        // Ensure the resource actually belongs to this post
        $belongs = DB::table('post_resources')
            ->where('post_id', $post_id)
            ->where('resource_id', $request->resource_id)
            ->exists();
        if (!$belongs) {
            \Log::error("Resource {$request->resource_id} not linked to post {$post_id}");
            return response()->json(['message' => 'Resource not part of this post'], 422);
        }


        $progress = PathProgress::updateOrCreate(
            [
                'follow_id'   => $follow->follow_id,
                'resource_id' => $request->resource_id,
            ],
            [
                'is_completed'       => $request->boolean('is_completed'),
                'completed_at'       => $request->boolean('is_completed') ? now() : null,
                'time_to_completion' => $request->input('time_to_completion'),
                'created_at'         => now(),
            ]
        );

        return response()->json([
            'message'  => 'Progress updated',
            'progress' => $progress,
        ]);
    }
}
