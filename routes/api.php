<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiaBanController;
use App\Http\Controllers\Api\DynamicController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ForceAppController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\GoogleController;
use App\Http\Controllers\Api\MapsController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportMetricController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SanctumController;
use App\Http\Controllers\Api\TableCategoryController;
use App\Http\Controllers\Api\TableColumnController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\ToChucController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WidgetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Authentication
Route::post('login', [SanctumController::class, 'login'])->name('api.login');
Route::post('register', [UserController::class, 'store'])->name('api.register.store');
Route::post('reset-password', [AuthController::class, 'resetPasswordSendEmail'])->name('api.auth.resetPasswordSendEmail');
Route::post('reset-password-update', [AuthController::class, 'resetPasswordUpdate'])->name('api.auth.resetPasswordUpdate');

// Google Login
Route::get('auth/google/url', [GoogleController::class, 'loginUrl'])->name('api.auth.google.loginUrl');
Route::get('auth/google/callback', [GoogleController::class, 'loginCallback'])->name('api.auth.google.loginCallback');

Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'me'])->name('api.me');
Route::middleware('auth:sanctum')->put('/me', [UserController::class, 'update'])->name('api.me.update');
Route::middleware('auth:sanctum')->post('/change-email-request', [UserController::class, 'changeEmailRequest'])->name('api.me.changeEmailRequest');
Route::middleware('auth:sanctum')->post('/change-email-confirm', [UserController::class, 'changeEmailConfirm'])->name('api.me.changeEmailConfirm');
Route::middleware('auth:sanctum')->post('/check-email', [UserController::class, 'checkEmail'])->name('api.checkEmail');
Route::middleware('auth:sanctum')->post('/resend-verify-email', [EmailVerificationController::class, 'resend'])->name('api.emailVerification.resend');


Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index'])->name('api.users.index');
Route::middleware('auth:sanctum')->get('/table-columns/{tableId}', [TableColumnController::class, 'index']);
Route::middleware('auth:sanctum')->resource('/dynamic', DynamicController::class);
Route::middleware('auth:sanctum')->resource('/roles', RoleController::class);
Route::middleware('auth:sanctum')->resource('/tables', TableController::class);
Route::middleware('auth:sanctum')->resource('/table-categories', TableCategoryController::class);
Route::middleware('auth:sanctum')->resource('/force-apps', ForceAppController::class);
Route::middleware('auth:sanctum')->post('/tables-validator', [TableController::class, 'validator'])->name('api.tables.validator');
Route::middleware('auth:sanctum')->resource('/forms', FormController::class);
Route::middleware('auth:sanctum')->resource('/maps', MapsController::class);
Route::middleware('auth:sanctum')->resource('/dia-bans', DiaBanController::class);
Route::middleware('auth:sanctum')->resource('/to-chucs', ToChucController::class);
Route::middleware('auth:sanctum')->resource('/report-metrics', ReportMetricController::class);
Route::middleware('auth:sanctum')->resource('/reports', ReportController::class);
Route::middleware('auth:sanctum')->get('/maps-tables', [MapsController::class, 'tables']);

Route::middleware('auth:sanctum')->get('/widgets/countBtsTheoHuyen', [WidgetController::class, 'countBtsTheoHuyen']);
// Route::get('/users', [UserController::class, 'tree']);
Route::get('/dia-ban-tree', [DiaBanController::class, 'tree']);