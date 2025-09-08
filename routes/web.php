<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\RazorPayController;
use App\Http\Controllers\SenangPayController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\FlutterwaveV3Controller;
use App\Http\Controllers\PaypalPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SslCommerzPaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Fix for asset paths that include /public/ prefix
Route::get('public/{path}', function ($path) {
    $file = public_path($path);
    if (file_exists($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        // Set appropriate content type
        $contentType = match($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            default => 'application/octet-stream'
        };
        
        // Clear any output buffer to prevent PHP warnings from being included
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Read file contents directly to avoid any PHP output contamination
        $contents = file_get_contents($file);
        
        return response($contents, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
            'Content-Length' => strlen($contents)
        ]);
    }
    abort(404);
})->where('path', '.*');

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DeliveryManController;

Route::post('/subscribeToTopic', [FirebaseController::class, 'subscribeToTopic']);
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('lang/{locale}', [HomeController::class, 'lang'])->name('lang');
Route::get('terms-and-conditions', [HomeController::class, 'terms_and_conditions'])->name('terms-and-conditions');
Route::get('about-us', [HomeController::class, 'about_us'])->name('about-us');
Route::get('contact-us', [HomeController::class, 'contact_us'])->name('contact-us');
Route::post('send-message', [HomeController::class, 'send_message'])->name('send-message');
Route::get('privacy-policy', [HomeController::class, 'privacy_policy'])->name('privacy-policy');
Route::get('cancelation', [HomeController::class, 'cancelation'])->name('cancelation');
Route::get('refund', [HomeController::class, 'refund_policy'])->name('refund');
Route::get('shipping-policy', [HomeController::class, 'shipping_policy'])->name('shipping-policy');
Route::post('newsletter/subscribe', [NewsletterController::class, 'newsLetterSubscribe'])->name('newsletter.subscribe');
Route::get('subscription-invoice/{id}', [HomeController::class, 'subscription_invoice'])->name('subscription_invoice');
Route::get('order-invoice/{id}', [HomeController::class, 'order_invoice'])->name('order_invoice');

// Removed duplicate 'login' route name to prevent route caching conflicts.
// The canonical login route is defined later as '/login/{login_url}' with name 'login'.
Route::post('login_submit', [LoginController::class, 'submit'])->name('login_post')->middleware('actch');
// NOTE: Logout route is defined later as the canonical '/logout' route.
// Avoid duplicate definitions that can cause routing/cache conflicts.
Route::get('/reload-captcha', [LoginController::class, 'reloadCaptcha'])->name('reload-captcha');
Route::get('/reset-password', [LoginController::class, 'reset_password_request'])->name('reset-password');
Route::post('/vendor-reset-password', [LoginController::class, 'vendor_reset_password_request'])->name('vendor-reset-password');
Route::get('/password-reset', [LoginController::class, 'reset_password'])->name('change-password');
Route::post('verify-otp', [LoginController::class, 'verify_token'])->name('verify-otp');
Route::post('reset-password-submit', [LoginController::class, 'reset_password_submit'])->name('reset-password-submit');
Route::get('otp-resent', [LoginController::class, 'otp_resent'])->name('otp_resent');

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', [PaymentController::class, 'payment'])->name('payment-mobile');
    Route::get('set-payment-method/{name}', [PaymentController::class, 'set_payment_method'])->name('set-payment-method');
});

Route::get('payment-success', [PaymentController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentController::class, 'fail'])->name('payment-fail');
Route::get('payment-cancel', [PaymentController::class, 'cancel'])->name('payment-cancel');

$is_published = 0;
try {
$full_data = include('Modules/Gateways/Addon/info.php');
$is_published = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {}

if (!$is_published) {
    Route::group(['prefix' => 'payment'], function () {

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
            Route::get('canceled', [StripePaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('callback', [RazorPayController::class, 'callback'])->name('callback')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [RazorPayController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'callback'])->name('response')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
        });

        //BKASH

        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback');

            // Refund Routes for bKash
            // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
            // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
        });

        //Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('payment', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback');
        });

        //MERCADOPAGO

        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::any('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('success', [MercadoPagoController::class, 'success'])->name('success');
            Route::get('failed', [MercadoPagoController::class, 'failed'])->name('failed');
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback');
        });

        //PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback');
            Route::any('response', [PaytabsController::class, 'response'])->name('response');
        });
    });
}


//Restaurant Registration
Route::group(['prefix' => 'vendor', 'as' => 'restaurant.'], function () {
    Route::get('apply', [VendorController::class, 'create'])->name('create');
    Route::post('apply', [VendorController::class, 'store'])->name('store');
    Route::get('get-all-modules', [VendorController::class, 'get_all_modules'])->name('get-all-modules');
    Route::get('get-module-type', [VendorController::class, 'get_modules_type'])->name('get-module-type');

    Route::get('back', [VendorController::class, 'back'])->name('back');
    Route::post('business-plan', [VendorController::class, 'business_plan'])->name('business_plan');
    Route::post('payment', [VendorController::class, 'payment'])->name('payment');
    Route::get('final-step', [VendorController::class, 'final_step'])->name('final_step');
});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', [DeliveryManController::class, 'create'])->name('create');
    Route::post('apply', [DeliveryManController::class, 'store'])->name('store');
});


// Login routes - FIXED by Project Doctor
Route::get('/login/{login_url}', [App\Http\Controllers\LoginController::class, 'login'])->name('login');
Route::post('/login_submit', [App\Http\Controllers\LoginController::class, 'submit'])->name('login.submit');
Route::get('/logout', [App\Http\Controllers\LoginController::class, 'logout'])->name('logout');
Route::post('/logout', [App\Http\Controllers\LoginController::class, 'logout']);

// Password reset routes
Route::post('/admin-password-reset-request', [App\Http\Controllers\LoginController::class, 'reset_password_request'])->name('admin.password.reset.request');
Route::post('/vendor-password-reset-request', [App\Http\Controllers\LoginController::class, 'vendor_reset_password_request'])->name('vendor.password.reset.request');

// NOTE: Do not redefine the root '/' route here.
// The named route 'home' is already defined above:
// Route::get('/', [HomeController::class, 'index'])->name('home');
// Defining another '/' route would shadow the named route and can cause
// RouteNotFoundException for 'home' in middleware and redirects.

// Emergency route duplication for login_submit removed to prevent duplicate named routes


// Public bulk import template downloads
Route::get('/bulk-template/items', function() {
    return response()->download(public_path('assets/items_multilang_template.csv'));
})->name('public.bulk-template.items');

Route::get('/bulk-template/items-multilang', function() {
    return response()->download(public_path('assets/items_multilang_extended_template.csv'));
})->name('public.bulk-template.items-multilang');
