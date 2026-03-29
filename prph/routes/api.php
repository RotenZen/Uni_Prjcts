<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


//think of this as browser page directory

//check versioning once project is done, might become useful

//register,login,check,logout,user, admin/user test, skills,skillid works good(checked via postman)

// Public routes (no auth required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check', function () {
    return response()->json(['message' => 'API check working']);
});


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']); // returns current logged in user
    Route::put('/user', [AuthController::class, 'updateUser']); //update user info
});
// admin role test
Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-test', function () {
    return response()->json(['message' => 'Hello Admin']);
});

// user role test
Route::middleware(['auth:sanctum', 'role:user'])->get('/user-test', function () {
    return response()->json(['message' => 'Hello User']);
});

use App\Http\Controllers\SkillController;

// Public skill routes
Route::get('/skills', [SkillController::class, 'index']);
Route::get('/skills/{id}', [SkillController::class, 'show']);

// Admin-only skill routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/skills', [SkillController::class, 'store']);
    Route::put('/skills/{id}', [SkillController::class, 'update']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);
});


use App\Http\Controllers\PostController;


Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);


Route::middleware(['auth:sanctum', 'role:user,admin'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/me/posts', [PostController::class, 'myPosts']);

});


use App\Http\Controllers\LikeController;

// Authenticated users can react to posts
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/{post_id}/react', [LikeController::class, 'react']);
});

// Anyone can view reaction count
Route::get('/posts/{post_id}/reactions', [LikeController::class, 'count']);


use App\Http\Controllers\CommentController;

// Public - anyone can view comments
Route::get('/posts/{post_id}/comments', [CommentController::class, 'index']);

// Authenticated users - can comment and delete
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/{post_id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy']);
});


use App\Http\Controllers\FollowPathController;

// Follow/unfollow paths (must be logged in)
Route::middleware('auth:sanctum')->group(function () {
    // Follow a post (path) & unfollow
    Route::post('/posts/{post_id}/follow', [FollowPathController::class, 'follow']);
    Route::delete('/posts/{post_id}/follow', [FollowPathController::class, 'unfollow']);

    // My followed paths & progress
    Route::get('/me/follows', [FollowPathController::class, 'myFollows']);
    Route::get('/me/follows/{post_id}/progress', [FollowPathController::class, 'showProgress']);
    Route::post('/me/follows/{post_id}/progress', [FollowPathController::class, 'setProgress']);
});

use App\Http\Controllers\ReportController;
//user report submit
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reports', [ReportController::class, 'store']);
});


// Admin-only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/flagged-posts', [ReportController::class, 'flaggedPosts']);
    Route::patch('/reports/{reportId}/status', [ReportController::class, 'updateStatus']);
});

use App\Http\Controllers\ReviewController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
});

use App\Http\Controllers\BlacklistedDomainController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/blacklist', [BlacklistedDomainController::class, 'index']);
    Route::post('/blacklist', [BlacklistedDomainController::class, 'store']);
    Route::delete('/blacklist/{domain}', [BlacklistedDomainController::class, 'destroy']);
});






