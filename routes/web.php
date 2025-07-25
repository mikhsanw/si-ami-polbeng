<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::middleware(['role:Admin'])->group(function () { 
        
    });
});
// Route::get('/admin/dashboard', [App\Http\Controllers\Backend\DashboardController::class, 'index'])->name('home');
Route::prefix('js')->as('js')->group(function () {
    Route::any('/{layout}/{page}/{file}', [Controller::class, 'javaScript']);
});
Route::get('file-stream/{code}', "Backend\FileController@publicFileStream");
Route::get('file-thumbnail/{code}', "Backend\FileController@publicFileThumbnailStream");