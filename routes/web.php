<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat', [ChatController::class, 'send']);
