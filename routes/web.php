<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MusicsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\GuestListController;
use App\Http\Controllers\InvitationsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ImageUploadersController;

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
