<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

use App\Http\Controllers\MessageController;

Route::get('/messages', [MessageController::class, 'index']);
Route::post('/messages/add-number', [MessageController::class, 'addNumber']);
Route::post('/messages/add-template', [MessageController::class, 'addTemplate']);
Route::post('/messages/send', [MessageController::class, 'sendMessage']);
