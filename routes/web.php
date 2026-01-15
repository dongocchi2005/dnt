<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Frontend
use App\Http\Controllers\Frontend\ServiceController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\ClearanceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

// Auth
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostContentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController as FrontProductController;
use App\Http\Controllers\Frontend\ProductReviewController;
use App\Http\Controllers\Frontend\ServiceOrderPaymentController as FrontServiceOrderPaymentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\ServiceOrderController as AdminServiceOrderController;
use App\Http\Controllers\Admin\ServiceOrderPaymentController as AdminServiceOrderPaymentController;
use App\Http\Controllers\Admin\ChatAnalyticsController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\AdminChatInboxController;
use App\Http\Controllers\FrontendChatController;
use App\Http\Controllers\ProductLandingController;
/*
|--------------------------------------------------------------------------
| FRONTEND
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/chat/send', [FrontendChatController::class, 'sendMessage'])->name('chat.send');

Route::middleware(['auth', 'not_blocked'])->group(function () {
    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/my-bookings', [BookingController::class, 'history'])->name('booking.history');
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

    // Payment routes
    Route::get('/payment/{bookingId}', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::post('/payment/order', [PaymentController::class, 'payOrder'])->name('payment.payOrder');
    Route::get('/payment/order/{orderId}', [PaymentController::class, 'payOrderById'])->name('payment.order');
    Route::post('/payment/upload-proof', [PaymentController::class, 'uploadPaymentProof'])->name('payment.uploadProof');
    Route::get('/payment/thank-you/{type}/{id}', [PaymentController::class, 'thankYou'])->name('payment.thankYou');

    // user dashboard
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');

    // Order history
    Route::get('/orders', [OrderController::class, 'history'])->name('orders.history');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/service-orders/{serviceOrder}/pay', [FrontServiceOrderPaymentController::class, 'store'])
        ->name('service-orders.pay');
});


Route::prefix('admin')
    ->middleware(['auth', 'is_admin'])
    ->name('admin.')
    ->group(function () {

        Route::put('/orders/{order}/mark-delivered',
            [AdminOrderController::class, 'markDelivered']
        )->name('orders.mark-delivered');

    });

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/chat-analytics', [ChatAnalyticsController::class, 'index'])->name('chat-analytics.index');
        Route::get('/chat-inbox', [AdminChatInboxController::class, 'index'])->name('chat-inbox.index');
        Route::get('/chat-sessions/{chatSession}', [AdminChatInboxController::class, 'show'])->name('chat-sessions.show');
        Route::post('/chat-sessions/{chatSession}/takeover', [AdminChatInboxController::class, 'takeover'])->name('chat-sessions.takeover');
        Route::post('/chat-sessions/{chatSession}/release', [AdminChatInboxController::class, 'release'])->name('chat-sessions.release');
        Route::post('/chat-sessions/{chatSession}/messages', [AdminChatInboxController::class, 'storeMessage'])->name('chat-sessions.messages.store');
        Route::post('/chat-sessions/{chatSession}/read', [AdminChatInboxController::class, 'markRead'])->name('chat-sessions.read');

        Route::resource('bookings', AdminBookingController::class);
        Route::resource('orders', AdminOrderController::class);
        Route::post('orders/{id}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
        Route::post('/orders/{order}/payment/confirm', [AdminOrderController::class, 'confirmPayment'])->name('orders.payment.confirm');
        Route::post('/orders/{order}/payment/reject', [AdminOrderController::class, 'rejectPayment'])->name('orders.payment.reject');
        Route::put('/orders/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.tracking.update');
        Route::resource('posts', PostController::class);
        Route::post('posts/content-image', [PostContentController::class, 'uploadImage'])
            ->name('posts.content-image');
        Route::resource('products', ProductController::class);
        Route::delete('products/{product}/gallery/{image}', [ProductController::class, 'destroyGalleryImage'])
            ->name('products.gallery.destroy');
        Route::resource('categories', CategoryController::class);
        Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
        Route::post('users/{user}/lock', [\App\Http\Controllers\Admin\AdminUserController::class, 'lock'])->name('users.lock');
        Route::post('users/{user}/unlock', [\App\Http\Controllers\Admin\AdminUserController::class, 'unlock'])->name('users.unlock');
        Route::resource('services', \App\Http\Controllers\Admin\AdminServiceController::class);
        Route::resource('knowledge-base', KnowledgeBaseController::class);

        Route::get('service-orders', [AdminServiceOrderController::class, 'index'])->name('service-orders.index');
        Route::get('service-orders/{serviceOrder}', [AdminServiceOrderController::class, 'show'])->name('service-orders.show');
        Route::post('service-orders/{serviceOrder}/received', [AdminServiceOrderController::class, 'markReceived'])->name('service-orders.received');
        Route::post('service-orders/{serviceOrder}/quoted', [AdminServiceOrderController::class, 'setQuoted'])->name('service-orders.quoted');
        Route::post('service-orders/{serviceOrder}/in-repair', [AdminServiceOrderController::class, 'markInRepair'])->name('service-orders.in-repair');
        Route::post('service-orders/{serviceOrder}/ready-to-return', [AdminServiceOrderController::class, 'markReadyToReturn'])->name('service-orders.ready-to-return');
        Route::post('service-orders/{serviceOrder}/completed', [AdminServiceOrderController::class, 'markCompleted'])->name('service-orders.completed');
        Route::post('service-orders/{serviceOrder}/shipments/outbound', [AdminServiceOrderController::class, 'createOutboundShipment'])
            ->name('service-orders.shipments.outbound');
        Route::post('service-orders/{serviceOrder}/payments', [AdminServiceOrderPaymentController::class, 'store'])
            ->name('service-orders.payments.store');

Route::post('/bookings/{booking}/payment/confirm', [AdminBookingController::class, 'confirmPayment'])
    ->name('bookings.payment.confirm');

Route::post('/bookings/{booking}/payment/reject', [AdminBookingController::class, 'rejectPayment'])
    ->name('bookings.payment.reject');
    });
    Route::post('/notifications/read/{id}', function ($id) {
    $noti = auth()->user()
        ->notifications()
        ->where('id', $id)
        ->first();

    if ($noti) {
        $noti->markAsRead();
    }

    return response()->noContent();
})->name('notifications.readOne');


/*
|--------------------------------------------------------------------------
| BLOCKED
|--------------------------------------------------------------------------
*/
Route::get('/blocked', function () {
    return view('auth.blocked');
})->name('blocked');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

  Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

Route::get('/services', [ServiceController::class, 'index'])->name('services');

// Frontend blog routes
Route::get('/tin-tuc', [\App\Http\Controllers\Frontend\BlogController::class, 'index'])->name('blog.index');
Route::get('/tin-tuc/{slug}', [\App\Http\Controllers\Frontend\BlogController::class, 'show'])->name('blog.show');

Route::get('/san-pham/landing', [ProductLandingController::class, 'index'])->name('products.landing');

Route::get('/blog', function () {
    return redirect()->route('blog.index', [], 301);
});
Route::get('/blog/{slug}', function (string $slug) {
    return redirect()->route('blog.show', ['slug' => $slug], 301);
});

// Frontend clearance routes
Route::get('/clearance', [\App\Http\Controllers\Frontend\ClearanceController::class, 'index'])->name('clearance.index');
Route::get('/clearance/{slug}', [\App\Http\Controllers\Frontend\ClearanceController::class, 'show'])
    ->name('clearance.show');


// Checkout (buy now)
Route::get('/checkout/buy-now/{product}', [CheckoutController::class, 'buyNow'])->name('checkout.buyNow');
Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');

// Product detail
Route::get('/products/{slug}', [FrontProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])
    ->middleware('auth')
    ->name('products.reviews.store');

// Cart routes
Route::get('/cart', [CartController::class,'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class,'add'])->name('cart.add');
Route::post('/cart/update-qty', [CartController::class,'updateQty'])->name('cart.updateQty'); // New endpoint
Route::post('/cart/update/{key}', [CartController::class,'update'])->name('cart.update');
Route::post('/cart/remove/{key}', [CartController::class,'remove'])->name('cart.remove');
Route::post('/cart/remove-selected', [CartController::class,'removeSelected'])->name('cart.removeSelected');
Route::post('/cart/clear', [CartController::class,'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class,'count'])->name('cart.count'); // AJAX badge

// Theme route
Route::post('/set-theme', function(Request $request) {
    $request->validate(['theme' => 'required|in:light,dark']);
    session(['theme' => $request->theme]);
    return response()->json(['success' => true]);
})->name('set.theme');
Route::post('/payment/upload-proof/order',
    [PaymentController::class, 'uploadOrderPaymentProof']
)->name('payment.uploadProof.order')->middleware('auth');
Route::post('/orders/{order}/upload-proof', 
    [OrderController::class, 'uploadProof']
)->name('orders.uploadProof');
