<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Skill;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Show all posts or search by skill/user/title (public)
     * Example: GET /api/posts?skill=python
     */
    public function index(Request $request) //implement sorting once workload decreases
    {
        $query = Post::with(['user', 'skill', 'resources']);

        if ($request->has('skill')) {
            $query->whereHas('skill', function ($q) use ($request) {
                $q->where('s_name', 'like', '%' . $request->skill . '%');
            });
        }

        if ($request->has('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('u_name', 'like', '%' . $request->user . '%');
            });
        }

        // 🔍 Universal search by title, description, or skill name
        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('skill', function ($sub) use ($search) {
                        $sub->where('s_name', 'like', "%{$search}%");
                    });
            });
        }


        return response()->json($query->get());
    }

    /**
     * Show a single post (public)
     */
    public function show($id)
    {
        $post = Post::with(['user', 'skill', 'resources'])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post);
    }

    /**
     * Create new post (authenticated user)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_name' => 'required|string|max:150',
            //making resources nullable so that posts can be created without them for testing
            // after test will make them required
            'resources' => 'required|array|min:1',
            'resources.*.type' => 'required|string|in:video,article,book,playlist,website,other',
            'resources.*.title' => 'required|string|max:255',
            'resources.*.url' => 'required|url',
            'resources.*.order_number' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Auto-create skill if missing
            $skill = Skill::firstOrCreate(
                ['s_name' => ucfirst(strtolower(trim($request->skill_name)))],
                ['description' => null]
            );

            // Create post
            $post = Post::create([
                'user_id' => $request->user()->user_id,
                'skill_id' => $skill->skill_id,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            // Attach resources
            foreach ($request->resources as $res) {
                $domain = parse_url($res['url'], PHP_URL_HOST);
                $isBlacklisted = DB::table('blacklisted_domains')->where('domain', $domain)->exists();

                if ($isBlacklisted) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Resource contains blacklisted domain: $domain"
                    ], 403);
                }

                // create or reuse resource
                $resource = Resource::firstOrCreate(
                    ['url' => $res['url']],
                    [
                        'type' => $res['type'],
                        'title' => $res['title'],
                        'domain' => $domain,
                    ]
                );

                $post->resources()->attach($resource->resource_id, [
                    'order_number' => $res['order_number'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Post created successfully',
                'post' => $post->load(['skill', 'resources'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating post', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Fetch all posts created by the authenticated user.
     * GET /api/me/posts
     */
    public function myPosts(Request $request)
    {
        $user = $request->user();

        $posts = Post::with(['user', 'skill', 'resources'])
            ->where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($posts);
    }


    /**
     * Update a post (only owner)
     */
    public function update(Request $request, $id)
    {
        $post = Post::where('post_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found or unauthorized'], 404);
        }

        $post->update($request->only('title', 'description'));

        return response()->json(['message' => 'Post updated successfully', 'post' => $post]);
    }

    /**
     * Delete post (owner or admin)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Admins can find any post
        $post = ($user->role === 'admin')
            ? Post::find($id)
            : Post::where('post_id', $id)
                ->where('user_id', $user->user_id)
                ->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found or unauthorized'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

}
