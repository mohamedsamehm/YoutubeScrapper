<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});


Route::get('/', [CourseController::class, 'index'])->name('home');

Route::post('/fetch', [CourseController::class, 'fetch'])->name('courses.fetch');