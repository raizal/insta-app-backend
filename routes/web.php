<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// This route should be accessed by the frontend before making any requests that need CSRF protection
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Validate CSRF token for all web routes
Route::middleware('web')->group(function () {
    // This route can be used to validate the CSRF token
    Route::get('/validate-csrf', function () {
        return response()->json(['message' => 'CSRF token is valid']);
    });
});

// Serve storage images for posts
Route::get('/img/post/{filename}', function ($filename) {
    $path = storage_path('app/public/posts/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $file = file_get_contents($path);
    $type = mime_content_type($path);
    
    return response($file, 200)->header('Content-Type', $type);
});

// Additional route to handle plural form
Route::get('/img/{directory}/{filename}', function ($directory, $filename) {
    $path = storage_path('app/public/' . $directory . '/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $file = file_get_contents($path);
    $type = mime_content_type($path);
    
    return response($file, 200)->header('Content-Type', $type);
});

// Group all web routes with a prefix
Route::prefix('web')->group(function () {
    // Public auth routes
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);

    // Protected routes
    Route::middleware('auth')->group(function () {
        // User routes
        Route::get('/user', [WebAuthController::class, 'user']);
        Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
        
        // Profile routes
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture']);
        Route::delete('/profile/picture', [ProfileController::class, 'removeProfilePicture']);
        Route::get('/profile/{username}', [ProfileController::class, 'getUserProfile']);
        
        // Follow routes
        Route::post('/users/{username}/follow', [FollowController::class, 'follow']);
        Route::post('/users/{username}/unfollow', [FollowController::class, 'unfollow']);
        Route::post('/users/{username}/toggle-follow', [FollowController::class, 'toggleFollow']);
        Route::get('/users/{username}/followers', [FollowController::class, 'getFollowersAndFollowing']);
        Route::get('/users/{username}/follow-status', [FollowController::class, 'checkFollowStatus']);
        
        // Post routes
        Route::resource('posts', PostController::class);
        Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']);
        Route::get('/feed', [PostController::class, 'getFeedPosts']);
        Route::get('/users/{username}/posts', [PostController::class, 'getUserPosts']);
        
        // Comment routes
        Route::resource('comments', CommentController::class, ['only' => ['destroy']]);
        Route::post('/posts/{id}/comment', [CommentController::class, 'addComment']);
        Route::get('/posts/{id}/comments', [CommentController::class, 'getPostComments']);
    });
});
