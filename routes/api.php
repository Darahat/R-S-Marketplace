<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\CustomerProfileApiController;

Route::prefix('v1')->group(function () {
    Route::get('/home', [HomeApiController::class, 'index']);
    Route::get('/category/{slug}', [HomeApiController::class, 'category']);
});
Route::group(['prefix' => 'customer', 'middleware' => 'auth:sanctum', 'web'], function () {

});
Route::group(['prefix' => 'customer', 'middleware' => ['auth:sanctum', 'web']], function () {
    Route::post('/profile/update', [CustomerProfileApiController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [CustomerProfileApiController::class, 'instant_photo_view'])->name('customer.profile.photo');
 
});