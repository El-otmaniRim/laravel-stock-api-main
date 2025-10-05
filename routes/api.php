<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderItemController;
use App\Http\Controllers\API\PDFController;
use App\Http\Controllers\API\AdminUserRoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('admin/customer-users', [AdminUserRoleController::class, 'customerUsers']);
Route::get('admin/suppliers', [AdminUserRoleController::class, 'fournisseurUsers']);

Route::apiResource('products', ProductController::class);
Route::put('admin/suppliers/{id}', [AdminUserRoleController::class, 'updateSupplier']);
Route::post('admin/suppliers', [AdminUserRoleController::class, 'addSupplier']);
Route::get('admin/orders', [AdminUserRoleController::class, 'allOrders']);
Route::get('admin/delivery-users', [AdminUserRoleController::class, 'deliveryUsers']);


// routes/api.php
Route::get('/admin/delivery-data', [OrderController::class, 'getDeliveryData']);

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('admin/{user}/assign-delivery', [AdminUserRoleController::class, 'assignDeliveryRole']);
    Route::post('admin/{user}/remove-delivery', [AdminUserRoleController::class, 'removeDeliveryRole']);
    Route::delete('admin/users/{id}', [AdminUserRoleController::class, 'deleteUser']);
    Route::delete('admin/orders/{id}', [AdminUserRoleController::class, 'deleteOrder']);
    Route::get('admin/users', [AdminUserRoleController::class, 'allUsers']);
    Route::post('/admin/orders/{id}/assign', [OrderController::class, 'assignOrderToDelivery']);
    Route::get('/pdf/users', [PDFController::class, 'users']);
    Route::get('/pdf/orders', [PDFController::class, 'orders']);
    Route::get('/pdf/products', [PDFController::class, 'products']);
    Route::get('All_Products', [ProductController::class, 'index']); // Allow admin to get all products
});
Route::get('orders', [OrderController::class, 'index']);
Route::patch('/admin/orders/{id}/status', [AdminUserRoleController::class, 'updateOrderStatus']); 

// Delivery routes
Route::middleware(['auth:sanctum', 'role:delivery'])->group(function () {
    Route::put('orders/{order}/take', [OrderController::class, 'takeOrder']);
    Route::get('/pdf/delivery/orders', [PDFController::class, 'deliveryOrders']);
});

// Customer routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('All_Products', [ProductController::class, 'index']);
    Route::get('products_details/{product}', [ProductController::class, 'show']);
    Route::get('/orders/payment-success', [OrderController::class, 'paymentSuccess']);
    Route::post('/orders/checkout', [OrderController::class, 'createCheckoutSession']);
    Route::get('/pdf/my-orders', [PDFController::class, 'myOrders']);
});