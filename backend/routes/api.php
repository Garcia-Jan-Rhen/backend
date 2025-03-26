<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\Auth\CustomerAuthController;
use App\Http\Controllers\API\Auth\EmployeeAuthController;

// Public Routes (Authentication & Product Browsing)
Route::post('customer/register', [CustomerAuthController::class, 'register']);
Route::post('customer/login', [CustomerAuthController::class, 'login']);
Route::post('employee/login', [EmployeeAuthController::class, 'login']);
Route::post('employee/register', [EmployeeAuthController::class, 'register']);

Route::get('products', [ProductController::class, 'index']); // List all products
Route::get('products/{product}', [ProductController::class, 'show']); // View single product
Route::get('products/search', [ProductController::class, 'search']); // Search products

// Routes that require authentication (Sanctum Middleware)
Route::middleware('auth:sanctum')->group(function () {
    
    // Employee-only routes (Product Management & Checkout Monitoring)
    Route::middleware('employee')->group(function () {
        
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        
        Route::get('checkouts', [CheckoutController::class, 'index']); // View all transactions
        Route::get('checkouts/{id}', [CheckoutController::class, 'show']); // View single transaction
    });

    // Customer-only routes (Cart & Checkout)
    Route::middleware('customer')->group(function () {
        Route::post('cart', [CartController::class, 'add']);
        Route::post('checkout', [CartController::class, 'checkout']);
    });

    // Logout routes
    Route::post('customer/logout', [CustomerAuthController::class, 'logout']);
    Route::post('employee/logout', [EmployeeAuthController::class, 'logout']);
});

// Sanctum CSRF cookie (for authentication)
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['message' => 'CSRF Cookie Set']);
});
