<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:web');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:web');
});

// Posts routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'getPosts']);
    Route::post('/', [PostController::class, 'createPost'])->middleware('auth:web');
    Route::get('/{id}', [PostController::class, 'getPost']);
    Route::put('/{id}', [PostController::class, 'updatePost'])->middleware('auth:web');
    Route::delete('/{id}', [PostController::class, 'deletePost'])->middleware('auth:web');
    
    // Post comments
    Route::get('/{postId}/comments', [CommentController::class, 'getPostComments']);
    Route::post('/{postId}/comments', [CommentController::class, 'createComment'])->middleware('auth:web');
    
    // Post likes
    Route::post('/{postId}/like', [LikeController::class, 'toggleLike'])->middleware('auth:web');
});

// Comments routes
Route::prefix('comments')->group(function () {
    Route::put('/{id}', [CommentController::class, 'updateComment'])->middleware('auth:web');
    Route::delete('/{id}', [CommentController::class, 'deleteComment'])->middleware('auth:web');
});

// Likes routes
Route::prefix('likes')->group(function() {
    Route::post('/', [LikeController::class, 'createLike'])->middleware('auth:web');
    Route::delete('/{id}', [LikeController::class, 'deleteLike'])->middleware('auth:web');
});

// Follows routes
Route::prefix('follows')->group(function(){
    Route::get('/', [FollowController::class, 'getFollows']);
    Route::post('/', [FollowController::class, 'createFollow'])->middleware('auth:web');
    Route::delete('/{id}', [FollowController::class, 'deleteFollow'])->middleware('auth:web');
});

// Users routes
Route::prefix('users')->group(function () {
    Route::get('/{userId}/followers', [FollowController::class, 'getFollowers']);
    Route::get('/{userId}/following', [FollowController::class, 'getFollowing']);
    Route::post('/{userId}/follow', [FollowController::class, 'toggleFollow'])->middleware('auth:web');
    Route::get('/{username}', [UserController::class, 'getUserByUsername']);
});

