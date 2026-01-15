<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\FrontendChatController as SessionChatController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\ProductVariantController as ApiProductVariantController;
use App\Http\Controllers\Api\OrderLookupController;
use App\Http\Controllers\Api\BookingSlotsController;
use App\Http\Controllers\Api\BookingCreateController;
use App\Http\Controllers\Api\ServiceOrderLookupController;

Route::post('/chat', [ChatController::class, 'send']);
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/chat/session', [SessionChatController::class, 'startSession']);
    Route::post('/chat/message', [SessionChatController::class, 'sendMessage']);
    Route::post('/chat/send', [SessionChatController::class, 'sendMessage']);
    Route::post('/ai/chat', [SessionChatController::class, 'sendMessage']);
    Route::get('/chat/messages', [SessionChatController::class, 'getMessages']);

    // New polling API for chat widget
    Route::get('/chat/session/{session}/meta', [SessionChatController::class, 'getSessionMeta']);
    Route::get('/chat/session/{session}/poll', [SessionChatController::class, 'pollMessages']);
    Route::post('/chat/session/{session}/message', [SessionChatController::class, 'sendUserMessage']);
    Route::get('/chat/session-status', [SessionChatController::class, 'sessionStatus']);
});

Route::post('/orders/lookup', OrderLookupController::class);
Route::post('/service-orders/lookup', ServiceOrderLookupController::class);
Route::post('/bookings/slots', BookingSlotsController::class);
Route::post('/bookings/create', BookingCreateController::class);

Route::get('/products/{id}', [ApiProductController::class, 'show']);
Route::get('/products/slug/{slug}', [ApiProductController::class, 'showBySlug']);
Route::get('/products/{product}/variants', [ApiProductVariantController::class, 'index']);
Route::get('/products/{product}/variant', [ApiProductVariantController::class, 'match']);
