<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\RentalsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ListCarsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PurchaseOrdController;
use App\Http\Controllers\PurchaseReqController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('api')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
    });
});

Route::middleware('auth:api')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/getUser', 'getUser');
            Route::get('/user', 'user');
            Route::post('/showUser/{user}', 'showUser');
            Route::post('/refreshToken', 'refreshToken');
            Route::post('/create', 'create');
            Route::post('/getSession', 'getSession');
            Route::post('/updateUser/{user}', 'update');
            Route::post('/updatePassword/{user}', 'updatePassword');
            Route::post('/updateAdmin/{user}', 'updateAdmin');
            Route::post('/delete/{id}', 'destroy');
        });
    });
    Route::controller(PurchaseReqController::class)->group(function () {
        Route::prefix('pr_req')->group(function () {
            Route::post('/create', 'create');
            Route::get('/', 'index');
            Route::get('/get_select', 'get_select');
            Route::post('/{id}', 'edit');
            Route::post('/send/{id}', 'send');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
        });
    });
    Route::controller(PurchaseOrdController::class)->group(function () {
        Route::prefix('po')->group(function () {
            Route::post('/create', 'store');
            Route::get('/', 'index');
            Route::post('/{id}', 'edit');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
        });
    });
    Route::controller(DepartmentController::class)->group(function () {
        Route::prefix('departments')->group(function () {
            Route::get('/', 'index');
            Route::get('/get_select', 'get_select');
            Route::post('/create', 'store');
            Route::get('/{id}', 'show');
            Route::post('/update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
        });
    });
    Route::controller(VendorController::class)->group(function () {
        Route::prefix('vendors')->group(function () {
            Route::get('/', 'index');
            Route::get('/get_select', 'get_select');
            Route::post('/create', 'store');
            Route::get('/{id}', 'show');
            Route::post('/update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
        });
    });
    Route::controller(ApprovalController::class)->group(function () {
        Route::prefix('approval')->group(function () {
            Route::get('/', 'index');
            Route::post('/{id}', 'edit');
            Route::post('/send/{id}', 'send');
            Route::post('/reject/{id}', 'reject');
            Route::post('/history/{id}', 'history');
        });
    });
});
