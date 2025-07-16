<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/catalog', [OrderController::class, 'catalog']);
Route::post('/create-order', [OrderController::class, 'createOrder']);
Route::post('/approve-order', [OrderController::class, 'approveOrder']);
