<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HomeApiController;
 use App\Http\Controllers\Api\CustomerProfileApiController;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;
Route::get('/metrics', function () {
    // Use Redis storage if PHP Redis extension is available, else fallback to InMemory
    if (class_exists('\\Redis')) {
        $registry = new CollectorRegistry(new Redis([
            'host' => '127.0.0.1',
            'port' => 6379,
        ]));
    } else {
        $registry = new CollectorRegistry(new InMemory());
    }
    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());

    return response($result, 200)
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});
// Route::prefix('v1')->group(function () {
//     Route::get('/home', [HomeApiController::class, 'index']);
//     Route::get('/category/{slug}', [HomeApiController::class, 'category']);
// });
Route::group(['prefix' => 'customer', 'middleware' => 'auth:sanctum', 'web'], function () {

});
Route::group(['prefix' => 'customer', 'middleware' => ['auth:sanctum', 'web']], function () {
    Route::post('/profile/update', [CustomerProfileApiController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [CustomerProfileApiController::class, 'instantPhotoView'])->name('customer.profile.photo');

});
