<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AccountTransactionController;
use App\Http\Controllers\Admin\Item\AddonController;
use App\Http\Controllers\Admin\AutomatedMessageController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerWalletController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseSettingController;
use App\Http\Controllers\Admin\DeliveryManController;
use App\Http\Controllers\Admin\DeliveryManDisbursementController;
use App\Http\Controllers\Admin\DriveMondController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExternalConfigurationController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LoyaltyPointController;
use App\Http\Controllers\Admin\OfflinePaymentMethodController;
use App\Http\Controllers\Admin\OrderCancelReasonController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OtherBannerController;
use App\Http\Controllers\Admin\ParcelCategoryController;
use App\Http\Controllers\Admin\ParcelController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ProvideDMEarningController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\SMSModuleController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\StoreDisbursementController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\WithdrawalMethodController;
use App\Http\Controllers\Admin\DeliveryMan\DeliveryManController as SubDeliveryManController;


Route::group(['namespace' => 'Admin', 'as' => 'admin.'], function () {

    Route::group(['middleware' => ['admin', 'current-module']], function () {
        Route::get('/test', function () {
        });
        // Route::get('drivemond-panel', [DriveMondController::class, 'drivemondExternalLogin'])->name('drivemond-panel');
        Route::get('get-all-stores', [VendorController::class, 'get_all_stores'])->name('get_all_stores');
        Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');
        Route::get('settings', [SystemController::class, 'settings'])->name('settings');
        Route::post('settings', [SystemController::class, 'settings_update']);
        Route::post('settings-password', [SystemController::class, 'settings_password_update'])->name('settings-password');
        Route::get('/get-store-data', [SystemController::class, 'store_data'])->name('get-store-data');
        Route::post('remove_image', [BusinessSettingsController::class, 'remove_image'])->name('remove_image');
        Route::get('system-currency', [SystemController::class, 'system_currency'])->name('system_currency');
        //dashboard
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('maintenance-mode', [SystemController::class, 'maintenance_mode'])->name('maintenance-mode');
        Route::get('landing-page', [SystemController::class, 'landing_page'])->name('landing-page');

        Route::group(['prefix' => 'parcel', 'as' => 'parcel.', 'middleware' => ['module:parcel']], function () {
            Route::get('category/status/{id}/{status}', [ParcelCategoryController::class, 'status'])->name('category.status');
            Route::resource('category', ParcelCategoryController::class);
            Route::get('orders/{status}', [ParcelController::class, 'orders'])->name('orders');
            Route::get('orders/export/{status}/{file_type}', [ParcelController::class, 'parcel_orders_export'])->name('parcel_orders_export');
            Route::get('details/{id}', [ParcelController::class, 'order_details'])->name('order.details');
            Route::get('settings', [ParcelController::class, 'settings'])->name('settings');
            Route::post('settings', [ParcelController::class, 'update_settings'])->name('update.settings');
            Route::get('dispatch/{status}', [ParcelController::class, 'dispatch_list'])->name('list');
            Route::post('instruction', [ParcelController::class, 'instruction'])->name('instruction');
            Route::get('/instruction/{id}/{status}', [ParcelController::class, 'instruction_status'])->name('instruction_status');
            Route::put('instruction_edit/', [ParcelController::class, 'instruction_edit'])->name('instruction_edit');
            Route::delete('instruction_delete/{id}', [ParcelController::class, 'instruction_delete'])->name('instruction_delete');
        });

        Route::group(['prefix' => 'dashboard-stats', 'as' => 'dashboard-stats.'], function () {
            Route::post('order', [DashboardController::class, 'order'])->name('order');
            Route::post('zone', [DashboardController::class, 'zone'])->name('zone');
            Route::post('user-overview', [DashboardController::class, 'user_overview'])->name('user-overview');
            Route::post('commission-overview', [DashboardController::class, 'commission_overview'])->name('commission-overview');
            Route::post('business-overview', [DashboardController::class, 'business_overview'])->name('business-overview');
        });

        Route::post('item/variant-price', [ItemController::class, 'variant_price'])->name('item.variant-price');

        Route::group(['prefix' => 'item', 'as' => 'item.', 'middleware' => ['module:item']], function () {
            Route::get('add-new', [ItemController::class, 'index'])->name('add-new');
            Route::post('variant-combination', [ItemController::class, 'variant_combination'])->name('variant-combination');
            Route::post('store', [ItemController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ItemController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [ItemController::class, 'update'])->name('update');
            Route::get('list', [ItemController::class, 'list'])->name('list');
            Route::delete('delete/{id}', [ItemController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [ItemController::class, 'status'])->name('status');
            Route::get('review-status/{id}/{status}', [ItemController::class, 'reviews_status'])->name('reviews.status');
            Route::post('search', [ItemController::class, 'search'])->name('search');
            Route::post('store/{store_id}/search', [ItemController::class, 'search_store'])->name('store-search');
            Route::get('reviews', [ItemController::class, 'review_list'])->name('reviews');
            // Route::post('reviews/search', [ItemController::class, 'review_search'])->name('reviews.search');
            Route::get('remove-image', [ItemController::class, 'remove_image'])->name('remove-image');
            Route::get('view/{id}', [ItemController::class, 'view'])->name('view');
            Route::get('store-item-export', [ItemController::class, 'store_item_export'])->name('store-item-export');
            Route::get('reviews-export', [ItemController::class, 'reviews_export'])->name('reviews_export');
            Route::get('item-wise-reviews-export', [ItemController::class, 'item_wise_reviews_export'])->name('item_wise_reviews_export');

            Route::get('new/item/list', [ItemController::class, 'approval_list'])->name('approval_list');
            Route::get('approved', [ItemController::class, 'approved'])->name('approved');
            Route::get('product_denied', [ItemController::class, 'deny'])->name('deny');
            Route::get('requested/item/view/{id}', [ItemController::class, 'requested_item_view'])->name('requested_item_view');
            Route::get('product-gallery', [ItemController::class, 'product_gallery'])->name('product_gallery');

            //ajax request
            Route::get('get-categories', [ItemController::class, 'get_categories'])->name('get-categories');
            Route::get('get-items', [ItemController::class, 'get_items'])->name('getitems');
            Route::get('get-items-flashsale', [ItemController::class, 'get_items_flashsale'])->name('getitems-flashsale');
            Route::post('food-variation-generate', [ItemController::class, 'food_variation_generator'])->name('food-variation-generate');
            Route::post('variation-generate', [ItemController::class, 'variation_generator'])->name('variation-generate');


            Route::get('export', [ItemController::class, 'export'])->name('export');

            //Mainul
            Route::get('get-variations', [ItemController::class, 'get_variations'])->name('get-variations');
            Route::get('get-stock', [ItemController::class, 'get_stock'])->name('get_stock');
            Route::post('stock-update', [ItemController::class, 'stock_update'])->name('stock-update');

            //Import and export
            Route::get('bulk-import', [ItemController::class, 'bulk_import_index'])->name('bulk-import');
            Route::post('bulk-import', [ItemController::class, 'bulk_import_data']);
            Route::get('bulk-export', [ItemController::class, 'bulk_export_index'])->name('bulk-export-index');
            Route::post('bulk-export', [ItemController::class, 'bulk_export_data'])->name('bulk-export');
        });

        Route::group(['prefix' => 'promotional-banner', 'as' => 'promotional-banner.', 'middleware' => ['module:banner']], function () {
            Route::get('add-new', [OtherBannerController::class, 'promotional_index'])->name('add-new');
            Route::get('add-video', [OtherBannerController::class, 'promotional_video'])->name('add-video');
            Route::post('store', [OtherBannerController::class, 'promotional_store'])->name('store');
            Route::get('edit/{id}', [OtherBannerController::class, 'promotional_edit'])->name('edit');
            Route::post('update/{id}', [OtherBannerController::class, 'promotional_update'])->name('update');
            Route::get('update-status/{id}/{status}', [OtherBannerController::class, 'promotional_status'])->name('update-status');
            Route::delete('delete/{banner}', [OtherBannerController::class, 'promotional_destroy'])->name('delete');
            Route::get('add-why-choose', [OtherBannerController::class, 'promotional_why_choose'])->name('add-why-choose');
            Route::post('why-choose/store', [OtherBannerController::class, 'why_choose_store'])->name('why-choose-store');
            Route::get('why-choose/edit/{id}', [OtherBannerController::class, 'why_choose_edit'])->name('why-choose-edit');
            Route::post('why-choose/update/{id}', [OtherBannerController::class, 'why_choose_update'])->name('why-choose-update');
            Route::get('why-choose/update-status/{id}/{status}', [OtherBannerController::class, 'why_choose_status'])->name('why-choose-status-update');
            Route::delete('why-choose/delete/{banner}', [OtherBannerController::class, 'why_choose_destroy'])->name('why-choose-delete');
            Route::post('video-content/store', [OtherBannerController::class, 'video_content_store'])->name('video-content-store');
            Route::post('video-image/store', [OtherBannerController::class, 'video_image_store'])->name('video-image-store');
        });

        Route::group(['prefix' => 'campaign', 'as' => 'campaign.', 'middleware' => ['module:campaign']], function () {
            Route::get('{type}/add-new', [CampaignController::class, 'index'])->name('add-new');
            Route::post('store/basic', [CampaignController::class, 'storeBasic'])->name('store-basic');
            Route::post('store/item', [CampaignController::class, 'storeItem'])->name('store-item');
            Route::get('{type}/edit/{campaign}', [CampaignController::class, 'edit'])->name('edit');
            Route::get('{type}/view/{campaign}', [CampaignController::class, 'view'])->name('view');
            Route::post('basic/update/{campaign}', [CampaignController::class, 'update'])->name('update-basic');
            Route::post('item/update/{campaign}', [CampaignController::class, 'updateItem'])->name('update-item');
            Route::get('remove-store/{campaign}/{store}', [CampaignController::class, 'remove_store'])->name('remove-store');
            Route::post('add-store/{campaign}', [CampaignController::class, 'addstore'])->name('addstore');
            Route::get('{type}/list', [CampaignController::class, 'list'])->name('list');
            Route::get('status/{type}/{id}/{status}', [CampaignController::class, 'status'])->name('status');
            Route::delete('delete/{campaign}', [CampaignController::class, 'delete'])->name('delete');
            Route::delete('item/delete/{campaign}', [CampaignController::class, 'delete_item'])->name('delete-item');
            Route::post('basic-search', [CampaignController::class, 'searchBasic'])->name('searchBasic');
            Route::post('item-search', [CampaignController::class, 'searchItem'])->name('searchItem');
            Route::get('store-confirmation/{campaign}/{id}/{status}', [CampaignController::class, 'store_confirmation'])->name('store_confirmation');
            Route::get('basic-campaign-export', [CampaignController::class, 'basic_campaign_export'])->name('basic_campaign_export');
            Route::get('item-campaign-export', [CampaignController::class, 'item_campaign_export'])->name('item_campaign_export');

        });


        Route::group(['prefix' => 'flash-sale', 'as' => 'flash-sale.'], function () {
            Route::get('add-new', [FlashSaleController::class, 'index'])->name('add-new');
            Route::post('store', [FlashSaleController::class, 'store'])->name('store');
            Route::get('edit/{id}', [FlashSaleController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [FlashSaleController::class, 'update'])->name('update');
            Route::get('publish/{id}/{publish}', [FlashSaleController::class, 'publish'])->name('publish');
            Route::delete('delete/{id}', [FlashSaleController::class, 'delete'])->name('delete');
            Route::get('add-product/{id}', [FlashSaleController::class, 'add_product'])->name('add-product');
            Route::post('store-product', [FlashSaleController::class, 'store_product'])->name('store-product');
            Route::delete('delete-product/{id}', [FlashSaleController::class, 'delete_product'])->name('delete-product');
            Route::get('status/{id}/{status}', [FlashSaleController::class, 'status_product'])->name('status-product');
        });

        Route::group(['prefix' => 'message', 'as' => 'message.'], function () {
            Route::get('list', [ConversationController::class, 'list'])->name('list');
            Route::post('store/{user_id}', [ConversationController::class, 'store'])->name('store');
            Route::get('view/{conversation_id}/{user_id}', [ConversationController::class, 'view'])->name('view');
        });




        Route::group(['prefix' => 'store', 'as' => 'store.'], function () {
            Route::get('get-stores-data/{store}', [VendorController::class, 'get_store_data'])->name('get-stores-data');
            Route::get('store-filter/{id}', [VendorController::class, 'store_filter'])->name('store-filter');
            // Fix duplicate route name: use a unique name for get-account-data
            Route::get('get-account-data/{store}', [VendorController::class, 'get_account_data'])->name('get-account-data');
            Route::get('get-stores', [VendorController::class, 'get_stores'])->name('get-stores');
            Route::get('get-providers', [VendorController::class, 'get_providers'])->name('get-providers');
            Route::get('get-addons', [VendorController::class, 'get_addons'])->name('get_addons');
            Route::group(['middleware' => ['module:store']], function () {
                Route::get('update-application/{id}/{status}', [VendorController::class, 'update_application'])->name('application');
                Route::get('add', [VendorController::class, 'index'])->name('add');
                Route::post('store', [VendorController::class, 'store'])->name('store');
                Route::get('edit/{id}', [VendorController::class, 'edit'])->name('edit');
                Route::post('update/{store}', [VendorController::class, 'update'])->name('update');
                Route::post('discount/{store}', [VendorController::class, 'discountSetup'])->name('discount');
                Route::post('update-settings/{store}', [VendorController::class, 'updateStoreSettings'])->name('update-settings');
                Route::post('update-meta-data/{store}', [VendorController::class, 'updateStoreMetaData'])->name('update-meta-data');
                Route::delete('delete/{store}', [VendorController::class, 'destroy'])->name('delete');
                Route::delete('clear-discount/{store}', [VendorController::class, 'cleardiscount'])->name('clear-discount');
                // Route::get('view/{store}', [VendorController::class, 'view'])->name('view_tab');
                Route::get('disbursement-export/{id}/{type}', [VendorController::class, 'disbursement_export'])->name('disbursement-export');
                Route::get('view/{store}/{tab?}/{sub_tab?}', [VendorController::class, 'view'])->name('view');
                Route::get('list', [VendorController::class, 'list'])->name('list');
                Route::get('pending-requests', [VendorController::class, 'pending_requests'])->name('pending-requests');
                Route::get('deny-requests', [VendorController::class, 'deny_requests'])->name('deny-requests');
                Route::post('search', [VendorController::class, 'search'])->name('search');
                Route::get('export', [VendorController::class, 'export'])->name('export');
                Route::get('store-wise-reviwe-export', [VendorController::class, 'store_wise_reviwe_export'])->name('store_wise_reviwe_export');
                Route::get('export/cash/{type}/{store_id}', [VendorController::class, 'cash_export'])->name('cash_export');
                Route::get('export/order/{type}/{store_id}', [VendorController::class, 'order_export'])->name('order_export');
                Route::get('export/withdraw/{type}/{store_id}', [VendorController::class, 'withdraw_trans_export'])->name('withdraw_trans_export');
                Route::get('status/{store}/{status}', [VendorController::class, 'status'])->name('status');
                Route::get('featured/{store}/{status}', [VendorController::class, 'featured'])->name('featured');
                Route::get('toggle-settings-status/{store}/{status}/{menu}', [VendorController::class, 'store_status'])->name('toggle-settings');
                Route::post('status-filter', [VendorController::class, 'status_filter'])->name('status-filter');



                Route::get('recommended-store', [VendorController::class, 'recommended_store'])->name('recommended_store');
                Route::get('recommended-store-add', [VendorController::class, 'recommended_store_add'])->name('recommended_store_add');
                Route::get('recommended-store-status/{id}/{status}', [VendorController::class, 'recommended_store_status'])->name('recommended_store_status');
                Route::delete('recommended-store-remove/{id}', [VendorController::class, 'recommended_store_remove'])->name('recommended_store_remove');
                Route::get('shuffle-recommended-store/{status}', [VendorController::class, 'shuffle_recommended_store'])->name('shuffle_recommended_store');

                Route::get('selected-stores', [VendorController::class, 'selected_stores'])->name('selected_stores');


                //Import and export
                Route::get('bulk-import', [VendorController::class, 'bulk_import_index'])->name('bulk-import');
                Route::post('bulk-import', [VendorController::class, 'bulletproof_store_import']);  // BULLETPROOF METHOD
                Route::post('bulk-import-legacy', [VendorController::class, 'bulk_import_data'])->name('legacy-import'); // Legacy fallback
                Route::get('bulletproof-import-status', [VendorController::class, 'getBulletproofImportStatus'])->name('bulletproof-import-status');
                Route::get('bulk-export', [VendorController::class, 'bulk_export_index'])->name('bulk-export-index');
                Route::post('bulk-export', [VendorController::class, 'bulk_export_data'])->name('bulk-export');
                //Store shcedule
                Route::post('add-schedule', [VendorController::class, 'add_schedule'])->name('add-schedule');
                Route::get('remove-schedule/{store_schedule}', [VendorController::class, 'remove_schedule'])->name('remove-schedule');
            });

            Route::group(['middleware' => ['module:withdraw_list']], function () {
                Route::post('withdraw-status/{id}', [VendorController::class, 'withdrawStatus'])->name('withdraw_status');
                Route::get('withdraw_list', [VendorController::class, 'withdraw'])->name('withdraw_list');
                Route::post('withdraw_search', [VendorController::class, 'withdraw_search'])->name('withdraw_search');
                Route::get('withdraw_export', [VendorController::class, 'withdraw_export'])->name('withdraw_export');
                Route::get('withdraw-view/{withdraw_id}/{seller_id}', [VendorController::class, 'withdraw_view'])->name('withdraw_view');
            });

            // message
            Route::get('message/{conversation_id}/{user_id}', [VendorController::class, 'conversation_view'])->name('message-view');
            Route::get('message/list', [VendorController::class, 'conversation_list'])->name('message-list');
        });


        Route::get('addon/system-addons', function (){
            return to_route('admin.system-addon.index');
        })->name('addon.index');

        Route::get('order/generate-invoice/{id}', [OrderController::class, 'generate_invoice'])->name('order.generate-invoice');
        Route::get('order/print-invoice/{id}', [OrderController::class, 'print_invoice'])->name('order.print-invoice');
        Route::get('order/status', [OrderController::class, 'status'])->name('order.status');
        Route::get('order/offline-payment', [OrderController::class, 'offline_payment'])->name('order.offline_payment');
        Route::group(['prefix' => 'order', 'as' => 'order.', 'middleware' => ['module:order']], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::get('details/{id}', [OrderController::class, 'details'])->name('details');
            Route::get('all-details/{id}', [OrderController::class, 'all_details'])->name('all-details');

            // Route::put('status-update/{id}', [OrderController::class, 'status'])->name('status-update');
            Route::get('view/{id}', [OrderController::class, 'view'])->name('view');
            Route::post('update-shipping/{order}', [OrderController::class, 'update_shipping'])->name('update-shipping');
            Route::delete('delete/{id}', [OrderController::class, 'delete'])->name('delete');

            Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'add_delivery_man'])->name('add-delivery-man');
            Route::get('payment-status', [OrderController::class, 'payment_status'])->name('payment-status');

            Route::post('add-payment-ref-code/{id}', [OrderController::class, 'add_payment_ref_code'])->name('add-payment-ref-code');
            Route::post('add-order-proof/{id}', [OrderController::class, 'add_order_proof'])->name('add-order-proof');
            Route::get('remove-proof-image', [OrderController::class, 'remove_proof_image'])->name('remove-proof-image');
            Route::get('store-filter/{store_id}', [OrderController::class, 'restaurnt_filter'])->name('store-filter');
            Route::get('filter/reset', [OrderController::class, 'filter_reset']);
            Route::post('filter', [OrderController::class, 'filter'])->name('filter');
            Route::get('search', [OrderController::class, 'search'])->name('search');
            Route::post('store/search', [OrderController::class, 'store_order_search'])->name('store-search');
            Route::get('store/export', [OrderController::class, 'store_order_export'])->name('store-export');
            //order update
            Route::post('add-to-cart', [OrderController::class, 'add_to_cart'])->name('add-to-cart');
            Route::post('remove-from-cart', [OrderController::class, 'remove_from_cart'])->name('remove-from-cart');
            Route::get('update/{order}', [OrderController::class, 'update'])->name('update');
            Route::get('edit-order/{order}', [OrderController::class, 'edit'])->name('edit');
            Route::get('quick-view', [OrderController::class, 'quick_view'])->name('quick-view');
            Route::get('quick-view-cart-item', [OrderController::class, 'quick_view_cart_item'])->name('quick-view-cart-item');
            Route::get('export-orders/{file_type}/{status}/{type}', [OrderController::class, 'export_orders'])->name('export');

            Route::get('offline/payment/list/{status}', [OrderController::class, 'offline_verification_list'])->name('offline_verification_list');

        });
        // Refund
        Route::group(['prefix' => 'refund', 'as' => 'refund.', 'middleware' => ['module:order']], function () {
            Route::get('settings', [OrderController::class, 'refund_settings'])->name('refund_settings');
            Route::get('refund_mode', [OrderController::class, 'refund_mode'])->name('refund_mode');
            Route::post('refund_reason', [OrderController::class, 'refund_reason'])->name('refund_reason');
            Route::get('/status/{id}/{status}', [OrderController::class, 'reason_status'])->name('reason_status');
            Route::put('reason_edit/', [OrderController::class, 'reason_edit'])->name('reason_edit');
            Route::delete('reason_delete/{id}', [OrderController::class, 'reason_delete'])->name('reason_delete');
            Route::put('order_refund_rejection/', [OrderController::class, 'order_refund_rejection'])->name('order_refund_rejection');
            Route::get('/{status}', [OrderController::class, 'list'])->name('refund_attr');
        });



        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:settings', 'actch']], function () {
            Route::get('business-setup/{tab?}', [BusinessSettingsController::class, 'business_index'])->name('business-setup');
            Route::get('react-setup', [BusinessSettingsController::class, 'react_setup'])->name('react-setup');
            Route::post('react-update', [BusinessSettingsController::class, 'react_update'])->name('react-update');
            Route::post('update-setup', [BusinessSettingsController::class, 'business_setup'])->name('update-setup');
            Route::post('update-landing-setup', [BusinessSettingsController::class, 'landing_page_settings_update'])->name('update-landing-setup');
            Route::delete('delete-custom-landing-page', [BusinessSettingsController::class, 'delete_custom_landing_page'])->name('delete-custom-landing-page');
            Route::post('update-dm', [BusinessSettingsController::class, 'update_dm'])->name('update-dm');
            Route::post('update-disbursement', [BusinessSettingsController::class, 'update_disbursement'])->name('update-disbursement');
            Route::post('update-websocket', [BusinessSettingsController::class, 'update_websocket'])->name('update-websocket');
            Route::post('update-store', [BusinessSettingsController::class, 'update_store'])->name('update-store');
            Route::post('update-order', [BusinessSettingsController::class, 'update_order'])->name('update-order');
            Route::post('update-priority', [BusinessSettingsController::class, 'update_priority'])->name('update-priority');
            Route::get('app-settings', [BusinessSettingsController::class, 'app_settings'])->name('app-settings');
            // Use a unique name for POST to avoid duplicate named route
            Route::POST('app-settings', [BusinessSettingsController::class, 'update_app_settings'])->name('app-settings.update');
            Route::get('pages/admin-landing-page-settings/{tab?}', [BusinessSettingsController::class, 'admin_landing_page_settings'])->name('admin-landing-page-settings');
            // Unique name for POST
            Route::POST('pages/admin-landing-page-settings/{tab}', [BusinessSettingsController::class, 'update_admin_landing_page_settings'])->name('admin-landing-page-settings.update');
            Route::get('promotional-status/{id}/{status}', [BusinessSettingsController::class, 'promotional_status'])->name('promotional-status');
            Route::get('pages/admin-landing-page-settings/promotional-section/edit/{id}', [BusinessSettingsController::class, 'promotional_edit'])->name('promotional-edit');
            Route::post('promotional-section/update/{id}', [BusinessSettingsController::class, 'promotional_update'])->name('promotional-update');
            Route::delete('banner/delete/{banner}', [BusinessSettingsController::class, 'promotional_destroy'])->name('promotional-delete');
            Route::get('feature-status/{id}/{status}', [BusinessSettingsController::class, 'feature_status'])->name('feature-status');
            Route::get('pages/admin-landing-page-settings/feature-list/edit/{id}', [BusinessSettingsController::class, 'feature_edit'])->name('feature-edit');
            Route::post('feature-section/update/{id}', [BusinessSettingsController::class, 'feature_update'])->name('feature-update');
            Route::delete('feature/delete/{feature}', [BusinessSettingsController::class, 'feature_destroy'])->name('feature-delete');
            Route::get('criteria-status/{id}/{status}', [BusinessSettingsController::class, 'criteria_status'])->name('criteria-status');
            Route::get('pages/admin-landing-page-settings/why-choose-us/criteria-list/edit/{id}', [BusinessSettingsController::class, 'criteria_edit'])->name('criteria-edit');
            Route::post('criteria-section/update/{id}', [BusinessSettingsController::class, 'criteria_update'])->name('criteria-update');
            Route::delete('admin/criteria/delete/{criteria}', [BusinessSettingsController::class, 'criteria_destroy'])->name('criteria-delete');
            Route::get('review-status/{id}/{status}', [BusinessSettingsController::class, 'review_status'])->name('review-status');
            Route::get('pages/admin-landing-page-settings/testimonials/review-list/edit/{id}', [BusinessSettingsController::class, 'review_edit'])->name('review-edit');
            Route::post('review-section/update/{id}', [BusinessSettingsController::class, 'review_update'])->name('review-update');
            Route::delete('review/delete/{review}', [BusinessSettingsController::class, 'review_destroy'])->name('review-delete');
            Route::get('pages/react-landing-page-settings/{tab?}', [BusinessSettingsController::class, 'react_landing_page_settings'])->name('react-landing-page-settings');
            // Unique name for POST
            Route::POST('pages/react-landing-page-settings/{tab?}',
                [BusinessSettingsController::class, 'update_react_landing_page_settings'])->name('react-landing-page-settings.update');
            Route::DELETE('react-landing-page-settings/{tab}/{key}', [BusinessSettingsController::class, 'delete_react_landing_page_settings'])->name('react-landing-page-settings-delete');
            Route::get('review-react-status/{id}/{status}', [BusinessSettingsController::class, 'review_react_status'])->name('review-react-status');
            Route::get('pages/react-landing-page-settings/testimonials/review-react-list/edit/{id}', [BusinessSettingsController::class, 'review_react_edit'])->name('review-react-edit');
            Route::post('review-react-section/update/{id}', [BusinessSettingsController::class, 'review_react_update'])->name('review-react-update');
            Route::delete('review-react/delete/{review}', [BusinessSettingsController::class, 'review_react_destroy'])->name('review-react-delete');
            Route::get('pages/flutter-landing-page-settings/{tab?}', [BusinessSettingsController::class, 'flutter_landing_page_settings'])->name('flutter-landing-page-settings');
            // Unique name for POST
            Route::POST('pages/flutter-landing-page-settings/{tab}', [BusinessSettingsController::class, 'update_flutter_landing_page_settings'])->name('flutter-landing-page-settings.update');
            Route::get('flutter-criteria-status/{id}/{status}', [BusinessSettingsController::class, 'flutter_criteria_status'])->name('flutter-criteria-status');
            Route::get('pages/flutter-landing-page-settings/special-criteria/edit/{id}', [BusinessSettingsController::class, 'flutter_criteria_edit'])->name('flutter-criteria-edit');
            Route::post('flutter-criteria-section/update/{id}', [BusinessSettingsController::class, 'flutter_criteria_update'])->name('flutter-criteria-update');
            Route::delete('flutter/criteria/delete/{criteria}', [BusinessSettingsController::class, 'flutter_criteria_destroy'])->name('flutter-criteria-delete');
            Route::get('landing-page-settings/{tab?}', [BusinessSettingsController::class, 'landing_page_settings'])->name('landing-page-settings');
            // Unique name for POST
            Route::POST('landing-page-settings/{tab}', [BusinessSettingsController::class, 'update_landing_page_settings'])->name('landing-page-settings.update');
            Route::DELETE('landing-page-settings/{tab}/{key}', [BusinessSettingsController::class, 'delete_landing_page_settings'])->name('landing-page-settings-delete');

            // Centerlize login
            Route::group(['prefix' => 'login-settings', 'as' => 'login-settings.'], function () {
                Route::get('login-setup', [BusinessSettingsController::class, 'login_settings'])->name('index');
                Route::post('login-setup/update', [BusinessSettingsController::class, 'login_settings_update'])->name('update');
            });

            Route::get('login-url-setup', [BusinessSettingsController::class, 'login_url_page'])->name('login_url_page');
            Route::post('login-url-setup/update', [BusinessSettingsController::class, 'login_url_page_update'])->name('login_url_update');

            Route::get('email-setup/{type}/{tab?}', [BusinessSettingsController::class, 'email_index'])->name('email-setup');
            // Unique name for POST
            Route::POST('email-setup/{type}/{tab?}', [BusinessSettingsController::class, 'update_email_index'])->name('email-setup.update');
            Route::get('email-status/{type}/{tab}/{status}', [BusinessSettingsController::class, 'update_email_status'])->name('email-status');

            Route::get('toggle-settings/{key}/{value}', [BusinessSettingsController::class, 'toggle_settings'])->name('toggle-settings');
            Route::get('site_direction', [BusinessSettingsController::class, 'site_direction'])->name('site_direction');


            Route::get('fcm-index', [BusinessSettingsController::class, 'fcm_index'])->name('fcm-index');
            Route::get('fcm-config', [BusinessSettingsController::class, 'fcm_config'])->name('fcm-config');
            Route::post('update-fcm', [BusinessSettingsController::class, 'update_fcm'])->name('update-fcm');

            Route::post('update-fcm-messages', [BusinessSettingsController::class, 'update_fcm_messages'])->name('update-fcm-messages');
            Route::post('update-fcm-messages-rental', [BusinessSettingsController::class, 'update_fcm_messages_rental'])->name('update-fcm-messages-rental');

            Route::get('currency-add', [BusinessSettingsController::class, 'currency_index'])->name('currency-add');
            Route::post('currency-add', [BusinessSettingsController::class, 'currency_store']);
            Route::get('currency-update/{id}', [BusinessSettingsController::class, 'currency_edit'])->name('currency-update');
            Route::put('currency-update/{id}', [BusinessSettingsController::class, 'currency_update']);
            Route::delete('currency-delete/{id}', [BusinessSettingsController::class, 'currency_delete'])->name('currency-delete');

            Route::get('pages/business-page/terms-and-conditions', [BusinessSettingsController::class, 'terms_and_conditions'])->name('terms-and-conditions');
            Route::post('pages/business-page/terms-and-conditions', [BusinessSettingsController::class, 'terms_and_conditions_update']);

            Route::get('pages/business-page/privacy-policy', [BusinessSettingsController::class, 'privacy_policy'])->name('privacy-policy');
            Route::post('pages/business-page/privacy-policy', [BusinessSettingsController::class, 'privacy_policy_update']);

            Route::get('pages/business-page/about-us', [BusinessSettingsController::class, 'about_us'])->name('about-us');
            Route::post('pages/business-page/about-us', [BusinessSettingsController::class, 'about_us_update']);

            Route::get('pages/business-page/refund', [BusinessSettingsController::class, 'refund_policy'])->name('refund');
            Route::post('pages/business-page/refund', [BusinessSettingsController::class, 'refund_update']);
            Route::get('pages/refund-policy/{status}', [BusinessSettingsController::class, 'refund_policy_status'])->name('refund-policy-status');

            Route::get('pages/business-page/cancelation', [BusinessSettingsController::class, 'cancellation_policy'])->name('cancelation');
            Route::post('pages/business-page/cancelation', [BusinessSettingsController::class, 'cancellation_policy_update']);
            Route::get('pages/cancellation-policy/{status}', [BusinessSettingsController::class, 'cancellation_policy_status'])->name('cancellation-policy-status');

            Route::get('pages/business-page/shipping-policy', [BusinessSettingsController::class, 'shipping_policy'])->name('shipping-policy');
            Route::post('pages/business-page/shipping-policy', [BusinessSettingsController::class, 'shipping_policy_update']);
            Route::get('pages/shipping-policy/{status}', [BusinessSettingsController::class, 'shipping_policy_status'])->name('shipping-policy-status');
            // Social media
            Route::get('social-media/fetch', [SocialMediaController::class, 'fetch'])->name('social-media.fetch');
            Route::get('social-media/status-update', [SocialMediaController::class, 'social_media_status_update'])->name('social-media.status-update');
            Route::resource('pages/social-media', SocialMediaController::class);


            Route::get('notification-setup', [BusinessSettingsController::class, 'notification_setup'])->name('notification_setup');
            Route::get('notification-status-change/{key}/{user_type}/{type}', [BusinessSettingsController::class, 'notification_status_change'])->name('notification_status_change');



            Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.'], function () {
                Route::get('/download/{file_name}/{storage?}', [FileManagerController::class, 'download'])->name('download');
                Route::get('/index/{folder_path?}/{storage?}', [FileManagerController::class, 'index'])->name('index');
                Route::post('/image-upload', [FileManagerController::class, 'upload'])->name('image-upload');
                Route::delete('/delete/{file_path}', [FileManagerController::class, 'destroy'])->name('destroy');
            });

            Route::group(['prefix' => 'external-system', 'as' => 'external-system.'], function () {
                Route::get('drivemond-configuration', [ExternalConfigurationController::class, 'index'])->name('drivemond-configuration');
                Route::post('update-drivemond-configuration', [ExternalConfigurationController::class, 'updateDrivemondConfiguration'])->name('update-drivemond-configuration');
            });
            Route::group(['prefix' => 'third-party', 'as' => 'third-party.'], function () {
                Route::get('sms-module', [SMSModuleController::class, 'sms_index'])->name('sms-module');
                Route::post('sms-module-update/{sms_module}', [SMSModuleController::class, 'sms_update'])->name('sms-module-update');
                Route::get('payment-method', [BusinessSettingsController::class, 'payment_index'])->name('payment-method');
                // Route::post('payment-method-update/{payment_method}', [BusinessSettingsController::class, 'payment_update'])->name('payment-method-update');
                Route::post('payment-method-update', [BusinessSettingsController::class, 'payment_config_update'])->name('payment-method-update');
                Route::get('config-setup', [BusinessSettingsController::class, 'config_setup'])->name('config-setup');
                Route::post('config-update', [BusinessSettingsController::class, 'config_update'])->name('config-update');
                Route::get('mail-config', [BusinessSettingsController::class, 'mail_index'])->name('mail-config');
                Route::get('test-mail', [BusinessSettingsController::class, 'test_mail'])->name('test');
                Route::post('mail-config', [BusinessSettingsController::class, 'mail_config']);
                Route::post('mail-config-status', [BusinessSettingsController::class, 'mail_config_status'])->name('mail-config-status');
                Route::get('send-mail', [BusinessSettingsController::class, 'send_mail'])->name('mail.send');
                // social media login
                Route::group(['prefix' => 'social-login', 'as' => 'social-login.'], function () {
                    Route::get('view', [BusinessSettingsController::class, 'viewSocialLogin'])->name('view');
                    Route::post('update/{service}', [BusinessSettingsController::class, 'updateSocialLogin'])->name('update');
                });
                //recaptcha
                Route::get('recaptcha', [BusinessSettingsController::class, 'recaptcha_index'])->name('recaptcha_index');
                Route::post('recaptcha-update', [BusinessSettingsController::class, 'recaptcha_update'])->name('recaptcha_update');
                //firebase-otp
                Route::get('firebase-otp', [BusinessSettingsController::class, 'firebase_otp_index'])->name('firebase_otp_index');
                Route::post('firebase-otp-update', [BusinessSettingsController::class, 'firebase_otp_update'])->name('firebase_otp_update');
                //file_system
                Route::get('storage-connection', [BusinessSettingsController::class, 'storage_connection_index'])->name('storage_connection_index');
                Route::post('storage-connection-update/{name}', [BusinessSettingsController::class, 'storage_connection_update'])->name('storage_connection_update');
            });
            // Offline payment Methods
            Route::get('/offline-payment', [OfflinePaymentMethodController::class, 'index'])->name('offline');
            Route::get('/offline-payment/new', [OfflinePaymentMethodController::class, 'create'])->name('offline.new');
            Route::post('/offline-payment/store', [OfflinePaymentMethodController::class, 'store'])->name('offline.store');
            Route::get('/offline-payment/edit/{id}', [OfflinePaymentMethodController::class, 'edit'])->name('offline.edit');
            Route::post('/offline-payment/update', [OfflinePaymentMethodController::class, 'update'])->name('offline.update');
            Route::post('/offline-payment/delete', [OfflinePaymentMethodController::class, 'delete'])->name('offline.delete');
            Route::get('/offline-payment/status/{id}', [OfflinePaymentMethodController::class, 'status'])->name('offline.status');



            //db clean
            Route::get('db-index', [DatabaseSettingController::class, 'db_index'])->name('db-index');
            Route::post('db-clean', [DatabaseSettingController::class, 'clean_db'])->name('clean-db');

            Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
                Route::get('', [LanguageController::class, 'index'])->name('index');
                Route::post('add-new', [LanguageController::class, 'store'])->name('add-new');
                Route::get('update-status', [LanguageController::class, 'update_status'])->name('update-status');
                Route::get('update-default-status', [LanguageController::class, 'update_default_status'])->name('update-default-status');
                Route::post('update', [LanguageController::class, 'update'])->name('update');
                Route::get('translate/{lang}', [LanguageController::class, 'translate'])->name('translate');
                Route::post('translate-submit/{lang}', [LanguageController::class, 'translate_submit'])->name('translate-submit');
                Route::post('remove-key/{lang}', [LanguageController::class, 'translate_key_remove'])->name('remove-key');
                Route::get('delete/{lang}', [LanguageController::class, 'delete'])->name('delete');
                Route::any('auto-translate/{lang}', [LanguageController::class, 'auto_translate'])->name('auto-translate');
                Route::get('auto-translate-all/{lang}', [LanguageController::class, 'auto_translate_all'])->name('auto_translate_all');

            });

            Route::get('order-cancel-reasons/status/{id}/{status}', [OrderCancelReasonController::class, 'status'])->name('order-cancel-reasons.status');
            Route::get('order-cancel-reasons', [OrderCancelReasonController::class, 'index'])->name('order-cancel-reasons.index');
            Route::post('order-cancel-reasons/store', [OrderCancelReasonController::class, 'store'])->name('order-cancel-reasons.store');
            Route::put('order-cancel-reasons/update', [OrderCancelReasonController::class, 'update'])->name('order-cancel-reasons.update');
            Route::delete('order-cancel-reasons/destroy/{id}', [OrderCancelReasonController::class, 'destroy'])->name('order-cancel-reasons.destroy');

            Route::post('automated-message/store', [AutomatedMessageController::class, 'store'])->name('automated_message.store');
            Route::put('automated-message/update', [AutomatedMessageController::class, 'update'])->name('automated_message.update');
            Route::get('automated-message/status/{id}/{status}', [AutomatedMessageController::class, 'status'])->name('automated_message.status');
            Route::delete('automated-message/destroy/{id}', [AutomatedMessageController::class, 'destroy'])->name('automated_message.destroy');

            Route::group(['namespace' => 'System','prefix' => 'system-addon', 'as' => 'system-addon.', 'middleware'=>['module:user_management']], function () {
                Route::get('/', [AddonController::class, 'index'])->name('index');
                Route::post('publish', [AddonController::class, 'publish'])->name('publish');
                Route::post('activation', [AddonController::class, 'activation'])->name('activation');
                Route::post('upload', [AddonController::class, 'upload'])->name('upload');
                Route::post('delete', [AddonController::class, 'delete_theme'])->name('delete');
            });

        });

        // Subscribed customer Routes
        Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {



            Route::group(['prefix' => 'wallet', 'as' => 'wallet.', 'middleware' => ['module:customer_wallet']], function () {
                Route::get('add-fund', [CustomerWalletController::class, 'add_fund_view'])->name('add-fund');
                Route::post('add-fund', [CustomerWalletController::class, 'add_fund']);
                Route::get('report', [CustomerWalletController::class, 'report'])->name('report');
            });
            Route::group(['middleware' => ['module:customer_management']], function () {

                // Subscribed customer Routes
                Route::get('subscribed', [CustomerController::class, 'subscribedCustomers'])->name('subscribed');
                // Route::post('subscriber-search', [CustomerController::class, 'subscriberMailSearch'])->name('subscriberMailSearch');
                Route::get('subscriber-search', [CustomerController::class, 'subscribed_customer_export'])->name('subscriber-export');

                Route::get('loyalty-point/report', [LoyaltyPointController::class, 'report'])->name('loyalty-point.report');
                Route::get('settings', [CustomerController::class, 'settings'])->name('settings');
                Route::post('update-settings', [CustomerController::class, 'update_settings'])->name('update-settings');
                Route::get('export', [CustomerController::class, 'export'])->name('export');
                Route::get('order-export', [CustomerController::class, 'customer_order_export'])->name('order-export');
                Route::get('trip-export', [CustomerController::class, 'customer_trip_export'])->name('trip-export');
            });
        });
        //Pos system
        Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::post('variant_price', [POSController::class, 'variant_price'])->name('variant_price');
            Route::group(['middleware' => ['module:pos']], function () {
                Route::get('/', [POSController::class, 'index'])->name('index');
                Route::get('quick-view', [POSController::class, 'quick_view'])->name('quick-view');
                Route::post('item-stock-view', [POSController::class, 'item_stock_view'])->name('item_stock_view');
                Route::post('item-stock-view-update', [POSController::class, 'item_stock_view_update'])->name('item_stock_view_update');
                Route::get('quick-view-cart-item', [POSController::class, 'quick_view_card_item'])->name('quick-view-cart-item');
                Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
                Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
                Route::post('cart-items', [POSController::class, 'cart_items'])->name('cart_items');
                Route::post('single-items', [POSController::class, 'single_items'])->name('single_items');
                Route::post('update-quantity', [POSController::class, 'updateQuantity'])->name('updateQuantity');
                Route::post('empty-cart', [POSController::class, 'emptyCart'])->name('emptyCart');
                Route::post('tax', [POSController::class, 'update_tax'])->name('tax');
                Route::post('discount', [POSController::class, 'update_discount'])->name('discount');
                Route::get('customers', [POSController::class, 'get_customers'])->name('customers');
                Route::post('order', [POSController::class, 'place_order'])->name('order');
                Route::get('invoice/{id}', [POSController::class, 'generate_invoice']);
                Route::post('customer-store', [POSController::class, 'customer_store'])->name('customer-store');
                Route::post('add-delivery-address', [POSController::class, 'addDeliveryInfo'])->name('add-delivery-address');
                Route::get('data', [POSController::class, 'extra_charge'])->name('extra_charge');
                Route::get('get-user-data', [POSController::class, 'getUserData'])->name('getUserData');
            });
        });

        Route::group(['prefix' => 'reviews', 'as' => 'reviews.', 'middleware' => ['module:customer_management']], function () {
            Route::get('list', [ReviewsController::class, 'list'])->name('list');
            Route::post('search', [ReviewsController::class, 'search'])->name('search');
        });

        Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
            Route::get('order', [ReportController::class, 'order_index'])->name('order');
            Route::get('transaction-report', [ReportController::class, 'day_wise_report'])->name('transaction-report');
            Route::get('item-wise-report', [ReportController::class, 'item_wise_report'])->name('item-wise-report');
            Route::get('item-wise-export', [ReportController::class, 'item_wise_export'])->name('item-wise-export');
            Route::post('item-wise-report-search', [ReportController::class, 'item_search'])->name('item-wise-report-search');
            Route::post('day-wise-report-search', [ReportController::class, 'day_search'])->name('day-wise-report-search');
            Route::get('day-wise-report-export', [ReportController::class, 'day_wise_export'])->name('day-wise-report-export');
            Route::get('order-transactions', [ReportController::class, 'order_transaction'])->name('order-transaction');
            Route::get('earning', [ReportController::class, 'earning_index'])->name('earning');
            Route::post('set-date', [ReportController::class, 'set_date'])->name('set-date');
            Route::get('stock-report', [ReportController::class, 'stock_report'])->name('stock-report');
            Route::post('stock-report', [ReportController::class, 'stock_search'])->name('stock-search');
            Route::get('stock-wise-report-search', [ReportController::class, 'stock_wise_export'])->name('stock-wise-report-export');
            Route::get('order-report', [ReportController::class, 'order_report'])->name('order-report');
            Route::post('order-report-search', [ReportController::class, 'search_order_report'])->name('search_order_report');
            Route::get('order-report-export', [ReportController::class, 'order_report_export'])->name('order-report-export');
            Route::get('store-wise-report', [ReportController::class, 'store_summary_report'])->name('store-summary-report');
            Route::post('store-summary-report-search', [ReportController::class, 'store_summary_search'])->name('store-summary-report-search');
            Route::get('store-summary-report-export', [ReportController::class, 'store_summary_export'])->name('store-summary-report-export');
            Route::get('store-wise-sales-report', [ReportController::class, 'store_sales_report'])->name('store-sales-report');
            Route::get('store-wise-sales-report-export', [ReportController::class, 'store_sales_export'])->name('store-sales-report-export');
            Route::get('store-wise-order-report', [ReportController::class, 'store_order_report'])->name('store-order-report');
            Route::post('store-wise-order-report-search', [ReportController::class, 'store_order_search'])->name('store-order-report-search');
            Route::get('store-wise-order-report-export', [ReportController::class, 'store_order_export'])->name('store-order-report-export');
            Route::get('expense-report', [ReportController::class, 'expense_report'])->name('expense-report');
            Route::get('expense-export', [ReportController::class, 'expense_export'])->name('expense-export');
            Route::post('expense-report-search', [ReportController::class, 'expense_search'])->name('expense-report-search');
            Route::get('generate-statement/{id}', [ReportController::class, 'generate_statement'])->name('generate-statement');
        });

        Route::get('customer/select-list', [CustomerController::class, 'get_customers'])->name('customer.select-list');


        Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:customer_management']], function () {
            Route::get('list', [CustomerController::class, 'customer_list'])->name('list');
            Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
            Route::post('search', [CustomerController::class, 'search'])->name('search');
            Route::get('status/{customer}/{status}', [CustomerController::class, 'status'])->name('status');
        });


        Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.'], function () {
            Route::get('/download/{file_name}/{storage?}', [FileManagerController::class, 'download'])->name('download');
            Route::get('/index/{folder_path?}//{storage?}', [FileManagerController::class, 'index'])->name('index');
            Route::post('/image-upload', [FileManagerController::class, 'upload'])->name('image-upload');
            Route::delete('/delete/{file_path}', [FileManagerController::class, 'destroy'])->name('destroy');
        });

        // social media login
        Route::group(['prefix' => 'social-login', 'as' => 'social-login.', 'middleware' => ['module:business_settings']], function () {
            Route::get('view', [BusinessSettingsController::class, 'viewSocialLogin'])->name('view');
            Route::post('update/{service}', [BusinessSettingsController::class, 'updateSocialLogin'])->name('update');
        });
        Route::group(['prefix' => 'apple-login', 'as' => 'apple-login.'], function () {
            Route::post('update/{service}', [BusinessSettingsController::class, 'updateAppleLogin'])->name('update');
        });
        Route::get('store/report', function () {
            return view('store_report');
        });

        Route::group(['prefix' => 'dispatch', 'as' => 'dispatch.'], function () {
            Route::get('/', [DashboardController::class, 'dispatch_dashboard'])->name('dashboard');
            Route::group(['middleware' => ['module:order']], function () {
                Route::get('list/{module?}/{status?}', [OrderController::class, 'dispatch_list'])->name('list');
                Route::get('parcel/list/{module?}/{status?}', [ParcelController::class, 'parcel_dispatch_list'])->name('parcel.list');
                Route::get('order/details/{id}', [OrderController::class, 'details'])->name('order.details');
                Route::get('order/generate-invoice/{id}', [OrderController::class, 'generate_invoice'])->name('order.generate-invoice');
            });
        });

        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [DashboardController::class, 'user_dashboard'])->name('dashboard');
            Route::get('disbursement-export/{id}/{type}', [SubDeliveryManController::class, 'disbursement_export'])->name('disbursement-export');
            Route::get('export', [SubDeliveryManController::class, 'exportList'])->name('export');

            // Subscribed customer Routes
            Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {


                Route::group(['prefix' => 'wallet', 'as' => 'wallet.', 'middleware' => ['module:customer_management']], function () {
                    Route::get('add-fund', [CustomerWalletController::class, 'add_fund_view'])->name('add-fund');
                    Route::post('add-fund', [CustomerWalletController::class, 'add_fund']);
                    Route::post('set-date', [CustomerWalletController::class, 'set_date'])->name('set-date');
                    Route::get('report', [CustomerWalletController::class, 'report'])->name('report');
                    Route::get('export', [CustomerWalletController::class, 'export'])->name('export');
                });

                Route::group(['middleware' => ['module:customer_management']], function () {

                    // Subscribed customer Routes
                    Route::get('subscribed', [CustomerController::class, 'subscribedCustomers'])->name('subscribed');
                    // Route::post('subscriber-search', [CustomerController::class, 'subscriberMailSearch'])->name('subscriberMailSearch');
                    Route::get('subscriber-search', [CustomerController::class, 'subscribed_customer_export'])->name('subscriber-export');

                    Route::get('loyalty-point/report', [LoyaltyPointController::class, 'report'])->name('loyalty-point.report');
                    Route::get('loyalty-point/export', [LoyaltyPointController::class, 'export'])->name('loyalty-point.export');
                    Route::post('loyalty-point/set-date', [LoyaltyPointController::class, 'set_date'])->name('loyalty-point.set-date');
                    Route::get('settings', [CustomerController::class, 'settings'])->name('settings');
                    Route::post('update-settings', [CustomerController::class, 'update_settings'])->name('update-settings');
                    Route::get('export', [CustomerController::class, 'export'])->name('export');
                    Route::get('order-export', [CustomerController::class, 'customer_order_export'])->name('order-export');
                });
            });
            Route::get('customer/select-list', [CustomerController::class, 'get_customers'])->name('customer.select-list');

            Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:customer_management']], function () {
                Route::get('list', [CustomerController::class, 'customer_list'])->name('list');
                Route::get('rental-view/{user_id}', [CustomerController::class, 'rentalView'])->name('rental.view');
                Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
                Route::post('search', [CustomerController::class, 'search'])->name('search');
                Route::get('status/{customer}/{status}file-manager', [CustomerController::class, 'status'])->name('status');
            });
            Route::group(['prefix' => 'contact', 'as' => 'contact.', 'middleware' => ['module:customer_management']], function () {
                Route::get('contact-list', [ContactController::class, 'list'])->name('contact-list');
                Route::get('contact-list-export', [ContactController::class, 'exportList'])->name('exportList');
                Route::delete('contact-delete/{id}', [ContactController::class, 'destroy'])->name('contact-delete');
                Route::get('contact-view/{id}', [ContactController::class, 'view'])->name('contact-view');
                Route::post('contact-update/{id}', [ContactController::class, 'update'])->name('contact-update');
                Route::post('contact-send-mail/{id}', [ContactController::class, 'send_mail'])->name('contact-send-mail');
                Route::post('contact-search', [ContactController::class, 'search'])->name('contact-search');
            });


        });
        Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
            Route::get('/', [DashboardController::class, 'transaction_dashboard'])->name('dashboard');
            Route::get('order/details/{id}', [OrderController::class, 'details'])->name('order.details');
            Route::get('parcel/order/details/{id}', [ParcelController::class, 'order_details'])->name('parcel.order.details');
            Route::get('order/generate-invoice/{id}', [OrderController::class, 'generate_invoice'])->name('order.generate-invoice');
            Route::get('customer/view/{user_id}', [CustomerController::class, 'view'])->name('customer.view');
            Route::get('item/view/{id}', [ItemController::class, 'view'])->name('item.view');
            Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
                Route::get('order', [ReportController::class, 'order_index'])->name('order');
                Route::get('day-wise-report', [ReportController::class, 'day_wise_report'])->name('day-wise-report');
                Route::get('item-wise-report', [ReportController::class, 'item_wise_report'])->name('item-wise-report');
                Route::get('item-wise-export', [ReportController::class, 'item_wise_export'])->name('item-wise-export');
                Route::post('item-wise-report-search', [ReportController::class, 'item_search'])->name('item-wise-report-search');
                Route::post('day-wise-report-search', [ReportController::class, 'day_search'])->name('day-wise-report-search');
                Route::get('day-wise-report-export', [ReportController::class, 'day_wise_export'])->name('day-wise-report-export');
                Route::get('order-transactions', [ReportController::class, 'order_transaction'])->name('order-transaction');
                Route::get('earning', [ReportController::class, 'earning_index'])->name('earning');
                Route::post('set-date', [ReportController::class, 'set_date'])->name('set-date');
                Route::get('stock-report', [ReportController::class, 'stock_report'])->name('stock-report');
                Route::post('stock-report', [ReportController::class, 'stock_search'])->name('stock-search');
                Route::get('stock-wise-report-search', [ReportController::class, 'stock_wise_export'])->name('stock-wise-report-export');
                Route::get('order-report', [ReportController::class, 'order_report'])->name('order-report');
                Route::post('order-report-search', [ReportController::class, 'search_order_report'])->name('search_order_report');
                Route::get('order-report-export', [ReportController::class, 'order_report_export'])->name('order-report-export');
                Route::get('store-wise-report', [ReportController::class, 'store_summary_report'])->name('store-summary-report');
                Route::post('store-summary-report-search', [ReportController::class, 'store_summary_search'])->name('store-summary-report-search');
                Route::get('store-summary-report-export', [ReportController::class, 'store_summary_export'])->name('store-summary-report-export');
                Route::get('store-wise-sales-report', [ReportController::class, 'store_sales_report'])->name('store-sales-report');
                Route::get('store-wise-sales-report-export', [ReportController::class, 'store_sales_export'])->name('store-sales-report-export');
                Route::get('store-wise-order-report', [ReportController::class, 'store_order_report'])->name('store-order-report');
                Route::post('store-wise-order-report-search', [ReportController::class, 'store_order_search'])->name('store-order-report-search');
                Route::get('store-wise-order-report-export', [ReportController::class, 'store_order_export'])->name('store-order-report-export');
                Route::get('expense-report', [ReportController::class, 'expense_report'])->name('expense-report');
                Route::get('expense-export', [ReportController::class, 'expense_export'])->name('expense-export');
                Route::post('expense-report-search', [ReportController::class, 'expense_search'])->name('expense-report-search');
                Route::get('low-stock-report', [ReportController::class, 'low_stock_report'])->name('low-stock-report');
                Route::post('low-stock-report', [ReportController::class, 'low_stock_search'])->name('low-stock-search');
                Route::get('low-stock-wise-report-search', [ReportController::class, 'low_stock_wise_export'])->name('low-stock-wise-report-export');
                Route::get('disbursement-report/{tab?}', [ReportController::class, 'disbursement_report'])->name('disbursement_report');
                Route::get('disbursement-report-export/{type}/{tab?}', [ReportController::class, 'disbursement_report_export'])->name('disbursement_report_export');
            });

            Route::group(['prefix' => 'account-transaction', 'as' => 'account-transaction.', 'middleware' => ['module:collect_cash']], function () {
                Route::get('list', [AccountTransactionController::class, 'index'])->name('index');
                Route::post('store', [AccountTransactionController::class, 'store'])->name('store');
                Route::get('details/{id}', [AccountTransactionController::class, 'show'])->name('view');
                Route::delete('delete/{id}', [AccountTransactionController::class, 'distroy'])->name('delete');
                Route::post('search', [EmployeeController::class, 'search'])->name('search');
                Route::get('export', [AccountTransactionController::class, 'export_account_transaction'])->name('export');
                Route::post('search', [AccountTransactionController::class, 'search_account_transaction'])->name('search');
            });

            Route::resource('provide-deliveryman-earnings', ProvideDMEarningController::class)->middleware('module:provide_dm_earning');
            Route::get('export-deliveryman-earnings', [ProvideDMEarningController::class, 'dm_earning_list_export'])->name('export-deliveryman-earning');
            Route::post('deliveryman-earnings-search', [ProvideDMEarningController::class, 'search_deliveryman_earning'])->name('search-deliveryman-earning');

            Route::group(['prefix' => 'store', 'as' => 'store.'], function () {
                Route::get('view/{store}/{tab?}/{sub_tab?}', [VendorController::class, 'view'])->name('view');
                Route::post('status-filter', [VendorController::class, 'status_filter'])->name('status-filter');
                Route::post('withdraw-status/{id}', [VendorController::class, 'withdrawStatus'])->name('withdraw_status');
                Route::get('withdraw_list', [VendorController::class, 'withdraw'])->name('withdraw_list');
                Route::post('withdraw_search', [VendorController::class, 'withdraw_search'])->name('withdraw_search');
                Route::get('withdraw_export', [VendorController::class, 'withdraw_export'])->name('withdraw_export');
                Route::get('withdraw-view/{withdraw_id}/{seller_id}', [VendorController::class, 'withdraw_view'])->name('withdraw_view');
                Route::get('get-Withdraw-Details', [VendorController::class, 'getWithdrawDetails'])->name('getWithdrawDetails');

            });

            Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
                Route::get('list', [WithdrawalMethodController::class, 'list'])->name('list');
                Route::get('create', [WithdrawalMethodController::class, 'create'])->name('create');
                Route::post('store', [WithdrawalMethodController::class, 'store'])->name('store');
                Route::get('edit/{id}', [WithdrawalMethodController::class, 'edit'])->name('edit');
                Route::put('update', [WithdrawalMethodController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [WithdrawalMethodController::class, 'delete'])->name('delete');
                Route::post('status-update', [WithdrawalMethodController::class, 'status_update'])->name('status-update');
                Route::post('default-status-update', [WithdrawalMethodController::class, 'default_status_update'])->name('default-status-update');
                Route::get('get-method-info', [WithdrawalMethodController::class, 'getMethodInfo'])->name('getMethodInfo');
            });

            Route::group(['prefix' => 'store-disbursement', 'as' => 'store-disbursement.', 'middleware' => ['module:account']], function () {
                Route::get('list', [StoreDisbursementController::class, 'list'])->name('list');
                Route::get('details/{id}', [StoreDisbursementController::class, 'view'])->name('view');
                Route::get('status', [StoreDisbursementController::class, 'status'])->name('status');
                Route::get('change-status/{id}/{status}', [StoreDisbursementController::class, 'statusById'])->name('change-status');
                Route::get('export/{id}/{type?}', [StoreDisbursementController::class, 'export'])->name('export');
            });
            Route::group(['prefix' => 'dm-disbursement', 'as' => 'dm-disbursement.', 'middleware' => ['module:account']], function () {
                Route::get('list', [DeliveryManDisbursementController::class, 'list'])->name('list');
                Route::get('details/{id}', [DeliveryManDisbursementController::class, 'view'])->name('view');
                Route::get('export/{id}/{type?}', [DeliveryManDisbursementController::class, 'export'])->name('export');
                Route::get('status', [DeliveryManDisbursementController::class, 'status'])->name('status');
                Route::get('change-status/{id}/{status}', [DeliveryManDisbursementController::class, 'statusById'])->name('change-status');
                Route::get('export/{id}/{type?}', [DeliveryManDisbursementController::class, 'export'])->name('export');
            });
        });
    });
});
