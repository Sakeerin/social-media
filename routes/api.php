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

// Route::prefix('/posts')->group(function () {
//     Route::get('/', [PostController::class, 'getPost']);
//     Route::post('/', [PostController::class, 'createPost']);
//     Route::put('/{id}', [PostController::class, 'updatePost'])->whereUuid('id');
//     Route::delete('/{id}', [PostController::class, 'deletePost']);
// });

// Route::prefix('/likes')->group(function() {
//     Route::post('/', [LikeController::class, 'createLike']);
//     Route::delete('/{id}', [LikeController::class, 'deleteLike']);
// });

// Route::prefix('/comments')->group(function(){
//     Route::put('/{id}', [CommentController::class, 'updateComment']);
//     Route::delete('/{id}', [CommentController::class, 'deleteComment']);
// });

// Route::prefix('/follows')->group(function(){
//     Route::get('/', [FollowController::class, 'getFollows']);
//     Route::post('/', [FollowController::class, 'createFollow']);
//     Route::put('/{id}', [FollowController::class, 'deleteFollow']);
// });

