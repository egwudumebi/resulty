<?php

use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ResultController::class, 'home'])->name('home');
Route::get('/results', [ResultController::class, 'results'])->name('results');
Route::post('/semester/preview', [ResultController::class, 'previewSemester'])->name('semester.preview');
Route::post('/semester/process', [ResultController::class, 'processSemester'])->name('semester.process');
Route::post('/session/process', [ResultController::class, 'processSession'])->name('session.process');
Route::post('/degree/process', [ResultController::class, 'processDegree'])->name('degree.process');
