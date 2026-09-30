<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\CompanyContactController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class,'resetPassword']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);

// Authenticated routes for user management
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/register', [AuthController::class, 'register']) ->middleware('role:super_admin');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/reset-password', [AuthController::class, 'resetPassword']) ->middleware('role:super_admin');
});


// Authenticated routes for galleries
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/galleries/mine', [GalleryController::class, 'mine']);
    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::match(['put', 'patch'], '/galleries/{gallery}', [GalleryController::class, 'update']);
    Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy']);
});

// Public routes for galleries
Route::get('/galleries', [GalleryController::class, 'index']);
Route::get('/galleries/{gallery}', [GalleryController::class, 'show']);


// Authenticated routes for articles and article categories
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/articles/mine', [ArticleController::class, 'mine']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::match(['put', 'patch'], '/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    // Article Category
    Route::post('/article-categories', [ArticleCategoryController::class, 'store']);
    Route::match(['put', 'patch'], '/article-categories/{articleCategory}', [ArticleCategoryController::class, 'update']);
    Route::delete('/article-categories/{articleCategory}', [ArticleCategoryController::class, 'destroy']);
});

// Public routes for articles and article categories
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
// Article Category
Route::get('/article-categories', [ArticleCategoryController::class, 'index']);
Route::get('/article-categories/{articleCategory}', [ArticleCategoryController::class, 'show']);


// Authenticated super_admin routes for company contacts
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::post('/company-contacts', [CompanyContactController::class, 'store']);
    Route::match(['put', 'patch'], '/company-contacts/{companyContact}', [CompanyContactController::class, 'update']);
    Route::delete('/company-contacts/{companyContact}', [CompanyContactController::class, 'destroy']);
});

// Public routes for company contacts
Route::get('/company-contacts', [CompanyContactController::class, 'index']);
Route::get('/company-contacts/{companyContact}', [CompanyContactController::class, 'show']);
