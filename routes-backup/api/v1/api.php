<?php

use App\WebSockets\Handler\DMLocationSocketHandler;
use Illuminate\Support\Facades\Route;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\V1\ExternalConfigurationController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\ZoneController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\DeliveryManLoginController;
use App\Http\Controllers\Api\V1\Auth\DMPasswordResetController;
use App\Http\Controllers\Api\V1\Auth\VendorLoginController;
use App\Http\Controllers\Api\V1\Auth\VendorPasswordResetController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\Vendor\SubscriptionController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\DeliverymanController;
use App\Http\Controllers\Api\V1\DeliveryManReviewController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\Vendor\VendorController;
use App\Http\Controllers\Api\V1\Vendor\WithdrawMethodController;
use App\Http\Controllers\Api\V1\Vendor\ReportController;
use App\Http\Controllers\Api\V1\Vendor\BusinessSettingsController;
use App\Http\Controllers\Api\V1\Vendor\AttributeController;
use App\Http\Controllers\Api\V1\Vendor\CouponController;
use App\Http\Controllers\Api\V1\Vendor\AdvertisementController;
use App\Http\Controllers\Api\V1\Vendor\AddOnController;
use App\Http\Controllers\Api\V1\Vendor\BannerController as VendorBannerController;
use App\Http\Controllers\Api\V1\Vendor\CategoryController as VendorCategoryController;
use App\Http\Controllers\Api\V1\Vendor\DeliveryManController as VendorDeliveryManController;
use App\Http\Controllers\Api\V1\Vendor\ItemController as VendorItemController;
use App\Http\Controllers\Api\V1\Vendor\POSController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Admin\Item\UnitController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\LoyaltyPointController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\OtherBannerController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommonConditionController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\FlashSaleController;
use App\Http\Controllers\Api\V1\CouponController as CustomerCouponController;
use App\Http\Controllers\Api\V1\CashBackController;
use App\Http\Controllers\Api\V1\ParcelCategoryController;
use App\Http\Controllers\Api\V1\AdvertisementController as CustomerAdvertisementController;

Route::group(['namespace' => 'Api\V1', 'middleware'=>'localization'], function () {
    Route::group(['prefix' => 'configurations'], function () {
        Route::get('/', [ExternalConfigurationController::class, 'getConfiguration']);
        Route::get('/get-external', [ExternalConfigurationController::class, 'getExternalConfiguration']);
        Route::post('/store', [ExternalConfigurationController::class, 'updateConfiguration']);
    });

    Route::get('/terms-and-conditions', [HomeController::class, 'terms_and_conditions']);
    Route::get('/about-us', [HomeController::class, 'about_us']);
    Route::get('/privacy-policy', [HomeController::class, 'privacy_policy']);
    Route::get('/refund-policy', [HomeController::class, 'refund_policy']);
    Route::get('/shipping-policy', [HomeController::class, 'shipping_policy']);
    Route::get('/cancelation', [HomeController::class, 'cancelation']);


    Route::get('zone/list', [ZoneController::class, 'get_zones']);
    Route::get('zone/check', [ZoneController::class, 'zonesCheck']);

    Route::get('offline_payment_method_list', [ConfigController::class, 'offline_payment_method_list']);
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('sign-up', [CustomerAuthController::class, 'register']);
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::post('verify-phone', [CustomerAuthController::class, 'verify_phone_or_email']);
        Route::post('update-info', [CustomerAuthController::class, 'update_info']);
        Route::post('firebase-verify-token', [CustomerAuthController::class, 'firebase_auth_verify']);

        Route::post('forgot-password', [PasswordResetController::class, 'reset_password_request']);
        Route::post('verify-token', [PasswordResetController::class, 'verify_token']);
        Route::put('reset-password', [PasswordResetController::class, 'reset_password_submit']);
        Route::put('firebase-reset-password', [PasswordResetController::class, 'firebase_auth_verify']);

        Route::post('guest/request',[CustomerAuthController::class, 'guest_request']);

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::post('login', [DeliveryManLoginController::class, 'login']);
            Route::post('store', [DeliveryManLoginController::class, 'store']);

            Route::post('forgot-password', [DMPasswordResetController::class, 'reset_password_request']);
            Route::post('verify-token', [DMPasswordResetController::class, 'verify_token']);
            Route::post('firebase-verify-token', [DMPasswordResetController::class, 'firebase_auth_verify']);
            Route::put('reset-password', [DMPasswordResetController::class, 'reset_password_submit']);
        });
        Route::group(['prefix' => 'vendor'], function () {
            Route::post('login', [VendorLoginController::class, 'login']);
            Route::post('forgot-password', [VendorPasswordResetController::class, 'reset_password_request']);
            Route::post('verify-token', [VendorPasswordResetController::class, 'verify_token']);
            Route::put('reset-password', [VendorPasswordResetController::class, 'reset_password_submit']);
            Route::post('register',[VendorLoginController::class, 'register']);
        });

        Route::post('social-login', [SocialAuthController::class, 'social_login']);
        Route::post('social-register', [SocialAuthController::class, 'social_register']);
    });

    //Store Subscription
    Route::group(['prefix' => 'vendor','namespace' => 'Vendor'], function () {
        Route::get('package-view', [SubscriptionController::class, 'package_view']);
        Route::post('business_plan', [SubscriptionController::class, 'business_plan']);
        Route::post('subscription/payment/api', [SubscriptionController::class, 'subscription_payment_api'])->name('subscription_payment_api');
        Route::post('package-renew', [SubscriptionController::class, 'package_renew_change_update_api']);
        Route::post('cancel-subscription', [SubscriptionController::class, 'cancelSubscription']);
        Route::get('check-product-limits', [SubscriptionController::class, 'checkProductLimits']);
    });

    // Module
    Route::get('module', [ModuleController::class, 'index']);

    Route::post('newsletter/subscribe',[NewsletterController::class, 'index']);
    Route::get('landing-page', [ConfigController::class, 'landing_page']);
    Route::get('react-landing-page', [ConfigController::class, 'react_landing_page']);
    Route::get('flutter-landing-page', [ConfigController::class, 'flutter_landing_page']);

    Route::group(['prefix' => 'delivery-man'], function () {
        Route::get('last-location', [DeliverymanController::class, 'get_last_location']);


        Route::group(['prefix' => 'reviews','middleware'=>['auth:api']], function () {
            Route::get('/{delivery_man_id}', [DeliveryManReviewController::class, 'get_reviews']);
            Route::get('rating/{delivery_man_id}', [DeliveryManReviewController::class, 'get_rating']);
            Route::post('/submit', [DeliveryManReviewController::class, 'submit_review']);
        });
        // Prefix route names within DM API to avoid global name collisions
        Route::group(['middleware'=>['dm.api'], 'as' => 'dm.'], function () {
            Route::get('profile', [DeliverymanController::class, 'get_profile']);
            Route::get('notifications', [DeliverymanController::class, 'get_notifications']);
            Route::put('update-profile', [DeliverymanController::class, 'update_profile']);
            Route::post('update-active-status', [DeliverymanController::class, 'activeStatus']);
            Route::get('current-orders', [DeliverymanController::class, 'get_current_orders']);
            Route::get('latest-orders', [DeliverymanController::class, 'get_latest_orders']);
            Route::post('record-location-data', [DeliverymanController::class, 'record_location_data']);
            Route::get('all-orders', [DeliverymanController::class, 'get_all_orders']);
            Route::get('order-delivery-history', [DeliverymanController::class, 'get_order_history']);
            Route::put('accept-order', [DeliverymanController::class, 'accept_order']);
            Route::put('update-order-status', [DeliverymanController::class, 'update_order_status']);
            Route::put('update-payment-status', [DeliverymanController::class, 'order_payment_status_update']);
            Route::get('order-details', [DeliverymanController::class, 'get_order_details']);
            Route::get('order', [DeliverymanController::class, 'get_order']);
            Route::put('send-order-otp', [DeliverymanController::class, 'send_order_otp']);
            Route::put('update-fcm-token', [DeliverymanController::class, 'update_fcm_token']);
            //Remove account
            Route::delete('remove-account', [DeliverymanController::class, 'remove_account']);



            Route::get('get-withdraw-method-list', [DeliverymanController::class, 'withdraw_method_list']);
            Route::get('get-disbursement-report', [DeliverymanController::class, 'disbursement_report']);

            Route::group(['prefix' => 'withdraw-method'], function () {
                Route::get('list', [DeliverymanController::class, 'get_disbursement_withdrawal_methods']);
                Route::post('store', [DeliverymanController::class, 'disbursement_withdrawal_method_store']);
                Route::post('make-default', [DeliverymanController::class, 'disbursement_withdrawal_method_default']);
                Route::delete('delete', [DeliverymanController::class, 'disbursement_withdrawal_method_delete']);
            });


            Route::post('make-collected-cash-payment', [DeliverymanController::class, 'make_payment'])->name('make_payment');
            Route::post('make-wallet-adjustment', [DeliverymanController::class, 'make_wallet_adjustment'])->name('make_wallet_adjustment');
            Route::get('wallet-payment-list', [DeliverymanController::class, 'wallet_payment_list'])->name('wallet_payment_list');
            Route::get('wallet-provided-earning-list', [DeliverymanController::class, 'wallet_provided_earning_list'])->name('wallet_provided_earning_list');


            // Chatting
            Route::group(['prefix' => 'message'], function () {
                Route::get('list', [ConversationController::class, 'dm_conversations']);
                Route::get('search-list', [ConversationController::class, 'dm_search_conversations']);
                Route::get('details', [ConversationController::class, 'dm_messages']);
                Route::post('send', [ConversationController::class, 'dm_messages_store']);
            });
        });
    });

    // Prefix vendor API route names to prevent collisions with other groups
    Route::group(['prefix' => 'vendor', 'namespace' => 'Vendor', 'middleware'=>['vendor.api'], 'as' => 'vendor.'], function () {
        Route::get('notifications', [VendorController::class, 'get_notifications']);
        Route::get('profile', [VendorController::class, 'get_profile']);
        Route::post('update-active-status', [VendorController::class, 'active_status']);
        Route::get('earning-info', [VendorController::class, 'get_earning_data']);
        Route::put('update-profile', [VendorController::class, 'update_profile']);
        Route::put('update-announcment', [VendorController::class, 'update_announcment']);
        Route::get('current-orders', [VendorController::class, 'get_current_orders']);
        Route::get('completed-orders', [VendorController::class, 'get_completed_orders']);
        Route::get('canceled-orders', [VendorController::class, 'get_canceled_orders']);
        Route::get('all-orders', [VendorController::class, 'get_all_orders']);
        Route::put('update-order-status', [VendorController::class, 'update_order_status']);
        Route::put('update-order-amount', [VendorController::class, 'edit_order_amount']);
        Route::get('order-details', [VendorController::class, 'get_order_details']);
        Route::get('order', [VendorController::class, 'get_order']);
        Route::put('update-fcm-token', [VendorController::class, 'update_fcm_token']);
        Route::get('get-basic-campaigns', [VendorController::class, 'get_basic_campaigns']);
        Route::put('campaign-leave', [VendorController::class, 'remove_store']);
        Route::put('campaign-join', [VendorController::class, 'addstore']);
        Route::get('get-withdraw-list', [VendorController::class, 'withdraw_list']);
        Route::get('get-items-list', [VendorController::class, 'get_items']);
        Route::put('update-bank-info', [VendorController::class, 'update_bank_info']);
        Route::post('request-withdraw', [VendorController::class, 'request_withdraw']);

        Route::put('send-order-otp', [VendorController::class, 'send_order_otp']);

        Route::post('make-collected-cash-payment', [VendorController::class, 'make_payment'])->name('make_payment');
        Route::post('make-wallet-adjustment', [VendorController::class, 'make_wallet_adjustment'])->name('make_wallet_adjustment');
        Route::get('wallet-payment-list', [VendorController::class, 'wallet_payment_list'])->name('wallet_payment_list');


        Route::get('get-withdraw-method-list', [WithdrawMethodController::class, 'withdraw_method_list']);

        Route::group(['prefix' => 'withdraw-method'], function () {
            Route::get('list', [WithdrawMethodController::class, 'get_disbursement_withdrawal_methods']);
            Route::post('store', [WithdrawMethodController::class, 'disbursement_withdrawal_method_store']);
            Route::post('make-default', [WithdrawMethodController::class, 'disbursement_withdrawal_method_default']);
            Route::delete('delete', [WithdrawMethodController::class, 'disbursement_withdrawal_method_delete']);
        });

        Route::get('get-expense', [ReportController::class, 'expense_report']);
        Route::get('get-disbursement-report', [ReportController::class, 'disbursement_report']);
        Route::get('subscription-transaction', [SubscriptionController::class, 'transaction']);



        //remove account
        Route::delete('remove-account', [VendorController::class, 'remove_account']);

        Route::get('unit',[UnitController::class, 'index']);
        // Business setup
        Route::put('update-business-setup', [BusinessSettingsController::class, 'update_store_setup']);

        // Reataurant schedule
        Route::post('schedule/store', [BusinessSettingsController::class, 'add_schedule']);
        Route::delete('schedule/{store_schedule}', [BusinessSettingsController::class, 'remove_schedule']);

        // Attributes
        Route::get('attributes', [AttributeController::class, 'list']);

        // Addon
        Route::group(['prefix'=>'coupon'], function(){
            Route::get('list', [CouponController::class, 'list']);
            Route::get('view', [CouponController::class, 'view']);
            Route::get('view-without-translate', [CouponController::class, 'view_without_translate']);
            Route::post('store', [CouponController::class, 'store'])->name('store');
            Route::post('update', [CouponController::class, 'update']);
            Route::post('status', [CouponController::class, 'status'])->name('status');
            Route::post('delete', [CouponController::class, 'delete'])->name('delete');
            Route::post('search', [CouponController::class, 'search'])->name('search');
        });
       // advertisement
        Route::group([ 'prefix' => 'advertisement', 'as' => 'ads.'], function () {
            Route::get('/', [AdvertisementController::class, 'index']);
            Route::get('details/{id}', [AdvertisementController::class, 'show']);
            Route::delete('delete/{id}', [AdvertisementController::class, 'destroy']);
            Route::post('store', [AdvertisementController::class, 'store']);
            Route::post('update/{id}', [AdvertisementController::class, 'update']);
            Route::put('/status', [AdvertisementController::class, 'status'])->name('status');
            Route::post('copy-add-post', [AdvertisementController::class, 'copyAddPost']);

        });

        // Addon
        Route::group(['prefix'=>'addon'], function(){
            Route::get('/', [AddOnController::class, 'list']);
            Route::post('store', [AddOnController::class, 'store']);
            Route::put('update', [AddOnController::class, 'update']);
            Route::get('status', [AddOnController::class, 'status']);
            Route::delete('delete', [AddOnController::class, 'delete']);
        });
        // Banner
        Route::group(['prefix'=>'banner'], function(){
            Route::get('/', [VendorBannerController::class, 'list']);
            Route::post('store', [VendorBannerController::class, 'store']);
            Route::put('update', [VendorBannerController::class, 'update']);
            Route::get('status', [VendorBannerController::class, 'status']);
            Route::delete('delete', [VendorBannerController::class, 'delete']);
        });
        //category
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [VendorCategoryController::class, 'get_categories']);
            Route::get('childes/{category_id}', [VendorCategoryController::class, 'get_childes']);
        });

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::post('store', [VendorDeliveryManController::class, 'store']);
            Route::get('list', [VendorDeliveryManController::class, 'list']);
            Route::get('preview', [VendorDeliveryManController::class, 'preview']);
            Route::get('status', [VendorDeliveryManController::class, 'status']);
            Route::post('update/{id}', [VendorDeliveryManController::class, 'update']);
            Route::delete('delete', [VendorDeliveryManController::class, 'delete']);
            Route::post('search', [VendorDeliveryManController::class, 'search']);
        });
        // Food
        Route::group(['prefix'=>'item'], function(){
            Route::post('store', [VendorItemController::class, 'store']);
            Route::put('update', [VendorItemController::class, 'update']);
            Route::delete('delete', [VendorItemController::class, 'delete']);
            Route::get('status', [VendorItemController::class, 'status']);
            Route::get('details/{id}', [VendorItemController::class, 'get_item']);
            Route::POST('search', [VendorItemController::class, 'search']);
            Route::get('reviews', [VendorItemController::class, 'reviews']);
            Route::put('reply-update', [VendorItemController::class, 'update_reply']);
            Route::get('recommended', [VendorItemController::class, 'recommended']);
            Route::get('organic', [VendorItemController::class, 'organic']);
            Route::get('pending/item/list', [VendorItemController::class, 'pending_item_list']);
            Route::get('requested/item/view/{id}', [VendorItemController::class, 'requested_item_view']);
            Route::put('stock-update', [VendorItemController::class, 'stock_update']);
            Route::get('stock-limit-list', [VendorItemController::class, 'stock_limit_list']);
        });

        // POS
        Route::group(['prefix'=>'pos'], function(){
            Route::get('orders', [POSController::class, 'order_list']);
            Route::post('place-order', [POSController::class, 'place_order']);
            Route::get('customers', [POSController::class, 'get_customers']);
        });

        // FLEET MANAGEMENT ROUTES - TEMPORARILY DISABLED FOR STABLE RELEASE
        // TODO: Re-enable after completing database migrations and proper testing
        // Route::group(['prefix' => 'delivery'], function () {
        //     Route::get('orders/{orderId}/available-drivers', 'VendorController@getAvailableDrivers');
        //     Route::post('orders/{orderId}/assign-driver', 'VendorController@assignDriverToOrder');
        // });

        // Chatting
        Route::group(['prefix' => 'message'], function () {
            Route::get('list', [ConversationController::class, 'conversations']);
            Route::get('search-list', [ConversationController::class, 'search_conversations']);
            Route::get('details', [ConversationController::class, 'messages']);
            Route::post('send', [ConversationController::class, 'messages_store']);
        });
    });

    Route::group(['prefix' => 'config'], function () {
        Route::get('/', [ConfigController::class, 'configuration']);
        Route::get('/get-zone-id', [ConfigController::class, 'get_zone']);
        Route::get('place-api-autocomplete', [ConfigController::class, 'place_api_autocomplete']);
        Route::get('distance-api', [ConfigController::class, 'distance_api']);
        Route::get('direction-api', [ConfigController::class, 'direction_api']);
        Route::get('place-api-details', [ConfigController::class, 'place_api_details']);
        Route::get('geocode-api', [ConfigController::class, 'geocode_api']);
        Route::get('get-PaymentMethods', [ConfigController::class, 'getPaymentMethods']);
    });

    Route::group(['prefix' => 'testimonial'], function () {
        Route::get('/', [TestimonialController::class, 'get_tetimonial_lists']);

    });

    Route::get('customer/order/cancellation-reasons', [OrderController::class, 'cancellation_reason']);
    Route::get('customer/automated-message', [OrderController::class, 'automatedMessage']);

    Route::get('item/get-generic-name-list', [ItemController::class, 'getGenericNameList']);
    Route::get('item/get-allergy-name-list', [ItemController::class, 'getAllergyNameList']);
    Route::get('item/get-nutrition-name-list', [ItemController::class, 'getNutritionNameList']);

    Route::get('customer/order/parcel-instructions', [OrderController::class, 'parcel_instructions']);
    Route::get('most-tips', [OrderController::class, 'most_tips']);
    Route::get('stores/details/{id}', [StoreController::class, 'get_details']);

    Route::group(['middleware'=>['module-check']], function(){
        Route::group(['prefix' => 'customer', 'middleware' => 'auth:api'], function () {
            Route::post('get-data', [CustomerController::class, 'getCustomer']);
            Route::post('external-update-data', [CustomerController::class, 'externalUpdateCustomer'])->withoutMiddleware(['auth:api','module-check']);
            Route::get('notifications', [NotificationController::class, 'get_notifications']);
            Route::get('info', [CustomerController::class, 'info']);
            Route::get('update-zone', [CustomerController::class, 'update_zone']);
            Route::post('update-profile', [CustomerController::class, 'update_profile']);
            Route::post('update-interest', [CustomerController::class, 'update_interest']);
            Route::put('cm-firebase-token', [CustomerController::class, 'update_cm_firebase_token']);
            Route::get('suggested-items', [CustomerController::class, 'get_suggested_item']);
            //Remove account
            Route::delete('remove-account', [CustomerController::class, 'remove_account']);

            Route::group(['prefix' => 'address'], function () {
                Route::get('list', [CustomerController::class, 'address_list']);
                Route::post('add', [CustomerController::class, 'add_new_address']);
                Route::put('update/{id}', [CustomerController::class, 'update_address']);
                Route::delete('delete', [CustomerController::class, 'delete_address']);
            });


            // Chatting
            Route::group(['prefix' => 'message'], function () {
                Route::get('list', [ConversationController::class, 'conversations']);
                Route::get('search-list', [ConversationController::class, 'search_conversations']);
                Route::get('details', [ConversationController::class, 'messages']);
                Route::post('send', [ConversationController::class, 'messages_store']);
            });

            Route::group(['prefix' => 'wish-list'], function () {
                Route::get('/', [WishlistController::class, 'wish_list']);
                Route::post('add', [WishlistController::class, 'add_to_wishlist']);
                Route::delete('remove', [WishlistController::class, 'remove_from_wishlist']);
            });

            //Loyalty
            Route::group(['prefix'=>'loyalty-point'], function() {
                Route::post('point-transfer', [LoyaltyPointController::class, 'point_transfer']);
                Route::get('transactions', [LoyaltyPointController::class, 'transactions']);
            });

            Route::group(['prefix'=>'wallet'], function() {
                Route::get('transactions', [WalletController::class, 'transactions']);
                Route::get('bonuses', [WalletController::class, 'get_bonus']);
                Route::post('add-fund', [WalletController::class, 'add_fund']);
                #handshake
                Route::post('transfer-mart-to-drivemond', [WalletController::class, 'transferMartToDrivemondWallet']);
                Route::post('transfer-mart-from-drivemond', [WalletController::class, 'transferMartFromDrivemondWallet'])->withoutMiddleware('auth:api');
            });

            Route::get('visit-again', [OrderController::class, 'order_again']);

            Route::get('review-reminder', [CustomerController::class, 'review_reminder']);
            Route::get('review-reminder-cancel', [CustomerController::class, 'review_reminder_cancel']);

        });
        Route::group(['prefix' => 'customer', 'middleware' => 'apiGuestCheck'], function () {
            Route::group(['prefix' => 'order'], function () {
                Route::get('list', [OrderController::class, 'get_order_list']);
                Route::get('running-orders', [OrderController::class, 'get_running_orders']);
                Route::get('details', [OrderController::class, 'get_order_details']);
                Route::post('place', [OrderController::class, 'place_order']);
                Route::post('prescription/place', [OrderController::class, 'prescription_place_order']);
                Route::put('cancel', [OrderController::class, 'cancel_order']);
                Route::post('refund-request', [OrderController::class, 'refund_request']);
                Route::get('refund-reasons', [OrderController::class, 'refund_reasons']);
                Route::get('track', [OrderController::class, 'track_order']);
                Route::put('payment-method', [OrderController::class, 'update_payment_method']);
                Route::put('offline-payment', [OrderController::class, 'offline_payment']);
                Route::put('offline-payment-update', [OrderController::class, 'update_offline_payment_info']);

            });

            Route::group(['prefix'=>'cart'], function() {
                Route::get('list', [CartController::class, 'get_carts']);
                Route::post('add', [CartController::class, 'add_to_cart']);
                Route::post('update', [CartController::class, 'update_cart']);
                Route::delete('remove-item', [CartController::class, 'remove_cart_item']);
                Route::delete('remove', [CartController::class, 'remove_cart']);
            });

        });

        Route::group(['prefix' => 'items'], function () {
            Route::get('latest', [ItemController::class, 'get_latest_products']);
            Route::get('new-arrival', [ItemController::class, 'get_new_products']);
            Route::get('popular', [ItemController::class, 'get_popular_products']);
            Route::get('most-reviewed', [ItemController::class, 'get_most_reviewed_products']);
            Route::get('discounted', [ItemController::class, 'get_discounted_products']);
            Route::get('set-menu', [ItemController::class, 'get_set_menus']);
            Route::get('search', [ItemController::class, 'get_searched_products']);
            Route::get('search-suggestion', [ItemController::class, 'get_searched_products_suggestion']);
            Route::get('details/{id}', [ItemController::class, 'get_product']);
            Route::get('related-items/{item_id}', [ItemController::class, 'get_related_products']);
            Route::get('related-store-items/{item_id}', [ItemController::class, 'get_related_store_products']);
            Route::get('reviews/{item_id}', [ItemController::class, 'get_product_reviews']);
            Route::get('rating/{item_id}', [ItemController::class, 'get_product_rating']);
            Route::get('recommended', [ItemController::class, 'get_recommended']);
            Route::get('basic', [ItemController::class, 'get_popular_basic_products']);
            Route::get('suggested', [ItemController::class, 'get_cart_suggest_products']);
            Route::get('item-or-store-search', [ItemController::class, 'item_or_store_search']);
            Route::post('reviews/submit', [ItemController::class, 'submit_product_review'])->middleware('auth:api');
            Route::get('common-conditions', [ItemController::class, 'get_store_condition_products']);
            Route::get('get-products', [ItemController::class, 'get_products']);
        });

        Route::group(['prefix' => 'stores'], function () {
            Route::get('get-stores/{filter_data}', [StoreController::class, 'get_stores']);
            Route::get('latest', [StoreController::class, 'get_latest_stores']);
            Route::get('popular', [StoreController::class, 'get_popular_stores']);
            Route::get('recommended', [StoreController::class, 'get_recommended_stores']);
            Route::get('discounted', [StoreController::class, 'get_discounted_stores']);
            Route::get('top-rated', [StoreController::class, 'get_top_rated_stores']);
            Route::get('popular-items/{id}', [StoreController::class, 'get_popular_store_items']);
            Route::get('reviews', [StoreController::class, 'reviews']);
            Route::get('search', [StoreController::class, 'get_searched_stores']);
            Route::get('get-data', [StoreController::class, 'get_combined_data']);
            Route::get('top-offer-near-me', [StoreController::class, 'get_top_offer_near_me']);
        });
        Route::get('get-combined-data', [SearchController::class, 'get_combined_data']);

        Route::group(['prefix' => 'banners'], function () {
            Route::get('/', [BannerController::class, 'get_banners']);
            Route::get('{store_id}/', [BannerController::class, 'get_store_banners']);
        });

        Route::group(['prefix' => 'other-banners'], function () {
            Route::get('/', [OtherBannerController::class, 'get_banners']);
            Route::get('video-content', [OtherBannerController::class, 'get_video_content']);
            Route::get('why-choose', [OtherBannerController::class, 'get_why_choose']);
        });

        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'get_categories']);
            Route::get('childes/{category_id}', [CategoryController::class, 'get_childes']);
            Route::get('items/list', [CategoryController::class, 'get_category_products']);
            Route::get('stores/list', [CategoryController::class, 'get_category_stores']);
            Route::get('items/{category_id}', [CategoryController::class, 'get_products']);
            Route::get('items/{category_id}/all', [CategoryController::class, 'get_all_products']);
            Route::get('stores/{category_id}', [CategoryController::class, 'get_stores']);
            Route::get('featured/items', [CategoryController::class, 'get_featured_category_products']);
            Route::get('popular', [CategoryController::class, 'get_popular_category_list']);
        });

        Route::group(['prefix' => 'common-condition'], function () {
            Route::get('/', [CommonConditionController::class, 'get_conditions']);
            Route::get('/list', [CommonConditionController::class, 'getCommonConditionList']);
            Route::get('items/{condition_id}', [CommonConditionController::class, 'get_products']);
        });

        Route::group(['prefix' => 'brand'], function () {
            Route::get('/', [BrandController::class, 'get_brands']);
            Route::get('items/{brand_id}', [BrandController::class, 'get_products']);
        });

        Route::group(['prefix' => 'campaigns'], function () {
            Route::get('basic', [CampaignController::class, 'get_basic_campaigns']);
            Route::get('basic-campaign-details', [CampaignController::class, 'basic_campaign_details']);
            Route::get('item', [CampaignController::class, 'get_item_campaigns']);
        });

        Route::group(['prefix' => 'flash-sales'], function () {
            Route::get('/', [FlashSaleController::class, 'get_flash_sales']);
            Route::get('/items', [FlashSaleController::class, 'get_flash_sale_items']);
        });

        Route::get('coupon/list/all', [CustomerCouponController::class, 'list']);
        Route::group(['prefix' => 'coupon', 'middleware' => 'auth:api'], function () {
            Route::get('list', [CustomerCouponController::class, 'list']);
            Route::get('apply', [CustomerCouponController::class, 'apply']);
        });
        Route::group(['prefix' => 'cashback', 'middleware' => 'auth:api'], function () {
            Route::get('list', [CashBackController::class, 'list']);
            Route::get('getCashback', [CashBackController::class, 'getCashback']);
        });

        Route::get('parcel-category',[ParcelCategoryController::class, 'index']);
        Route::get('advertisement/list', [CustomerAdvertisementController::class, 'get_adds']);

    });
    Route::get('vehicle/extra_charge', [ConfigController::class, 'extra_charge']);
    Route::get('get-vehicles', [ConfigController::class, 'get_vehicles']);
});

WebSocketsRouter::webSocket('/delivery-man/live-location', DMLocationSocketHandler::class);
