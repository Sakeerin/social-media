<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('/posts')->group(function () {
    Route::get('/', [PostController::class, 'getPost']);
    Route::post('/', [PostController::class, 'createPost']);
    // Route::get('/{id}', [PostController::class, 'getPostById']);
    Route::put('/{id}', [PostController::class, 'updatePost'])->whereUuid('id');
    Route::delete('/{id}', [PostController::class, 'deletePost']);
});

// Route::apiResource('posts',PostController::class)->except('destroy');

Route::post('/likes', [LikeController::class, 'createLike']);
Route::delete('/likes/{id}', [LikeController::class, 'deleteLike']);

Route::put('/comments/{id}', [CommentController::class, 'updateComment']);
Route::delete('/comments/{id}', [CommentController::class, 'deleteComment']);

Route::get('/follows', [FollowController::class, 'getFollows']);
Route::post('/follows', [FollowController::class, 'createFollow']);
Route::put('/follows/{id}', [FollowController::class, 'deleteFollow']);
// Route::delete('/follows/{id}', [FollowController::class, 'deleteFollow']);
