<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Store a new report (user action).
     * Validates input, ensures no duplicate reports,
     * and auto-flags posts once they reach the threshold.
     */
    public function store(Request $request)
    {
        // Validate incoming request (user_id not needed)
        $data = $request->validate([
            'post_id' => ['required', 'integer', 'exists:posts,post_id'],
            'reason'  => ['required', 'string', 'min:5'],
        ]);

        // Get the currently authenticated user
        $userId = $request->user()->user_id;

        // Prevent duplicate reports by the same user for the same post
        $already = Report::where('post_id', $data['post_id'])
            ->where('user_id', $userId)
            ->exists();

        if ($already) {
            return response()->json([
                'message' => 'You have already reported this post.'
            ], 409);
        }

        // Create the report
        $report = Report::create([
            'post_id' => $data['post_id'],
            'user_id' => $userId,
            'reason'  => $data['reason'],
            'status'  => 'pending',
        ]);

        // Check if the post needs to be flagged
        $this->maybeFlagPost($data['post_id']);

        return response()->json([
            'message' => 'Report submitted successfully.',
            'report'  => $report,
        ], 201);
    }


    /**
     * Admin: view all reports with optional filters.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status'       => ['sometimes', 'in:pending,reviewed,action_taken'],
            'post_id'      => ['sometimes', 'integer'],
            'user_id'      => ['sometimes', 'integer'],
            'flagged_only' => ['sometimes', 'boolean'],
            'per_page'     => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Report::with(['post:post_id,title,is_flagged', 'user:user_id,u_name']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['post_id'])) {
            $query->where('post_id', $filters['post_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['flagged_only']) && $filters['flagged_only']) {
            $query->whereHas('post', function ($q) {
                $q->where('is_flagged', true);
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        return response()->json($query->orderByDesc('report_id')->paginate($perPage));
    }

    /**
     * Admin: get all flagged posts with total report counts.
     */
    public function flaggedPosts(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $posts = Post::where('is_flagged', true)
            ->select(['post_id', 'title', 'is_flagged'])
            ->withCount('reports')
            ->orderByDesc('reports_count')
            ->paginate($perPage);

        return response()->json($posts);
    }

    /**
     * Admin: update report status and optionally unflag the post.
     */
    public function updateStatus($reportId, Request $request)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,reviewed,action_taken'],
            'unflag' => ['sometimes', 'boolean'],
        ]);

        $report = Report::with('post')->findOrFail($reportId);

        $report->status = $data['status'];
        $report->save();

        // If unflag requested, unflag the associated post
        if (!empty($data['unflag']) && $data['unflag']) {
            if ($report->post && $report->post->is_flagged) {
                $report->post->is_flagged = false;
                $report->post->save();
            }
        } else {
            // Recalculate and maintain flag consistency
            if ($report->post) {
                $this->maybeFlagPost($report->post->post_id);
            }
        }

        return response()->json([
            'message' => 'Report status updated successfully.',
            'report'  => $report,
        ]);
    }

    /**
     * Internal helper: Flags a post if it exceeds threshold.
     */
    protected function maybeFlagPost(int $postId): void
    {
        $threshold = (int) (config('reporting.flag_threshold') ?? 5);

        $count = Report::where('post_id', $postId)->count();

        Post::where('post_id', $postId)
            ->update(['is_flagged' => $count >= $threshold]);
    }
}
