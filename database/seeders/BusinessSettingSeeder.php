<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\CentralLogics\Helpers;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Helpers::insert_business_settings_key("mobile_app_section_heading" , "Download the App for Enjoy Best Restaurant Test");
        Helpers::insert_business_settings_key("mobile_app_section_text" , "Default Text Mobile App Section");
        Helpers::insert_business_settings_key("feature_section_description" , "Feature section description");
        Helpers::insert_business_settings_key("Feature section description", json_encode([
            "app_url_android_status" => "0",
            "app_url_android" => "https://play.google.com",
            "app_url_ios_status" => "0",
            "app_url_ios" => "https://www.apple.com/app-store",
            "web_app_url_status" => "0",
            "web_app_url" => "https://6ammart-web.6amtech.com/"
        ]));

        //version 1.5.0
        Helpers::insert_business_settings_key("wallet_status" , "0");
        Helpers::insert_business_settings_key("loyalty_point_status" , "0");
        Helpers::insert_business_settings_key("ref_earning_status" , "0");
        Helpers::insert_business_settings_key("wallet_add_refund" , "0");
        Helpers::insert_business_settings_key("loyalty_point_exchange_rate" , "0");
        Helpers::insert_business_settings_key("ref_earning_exchange_rate" , "0");
        Helpers::insert_business_settings_key("loyalty_point_item_purchase_point" , "0");
        Helpers::insert_business_settings_key("loyalty_point_minimum_point" , "0");
        Helpers::insert_business_settings_key("dm_tips_status" , "0");
        Helpers::insert_business_settings_key('tax_included', '0');
        Helpers::insert_business_settings_key('refund_active_status', '1');
        Helpers::insert_business_settings_key('social_login','[{"login_medium":"google","client_id":"","client_secret":"","status":"0"},{"login_medium":"facebook","client_id":"","client_secret":"","status":""}]');
        Helpers::insert_business_settings_key('system_language','[{"id":1,"direction":"ltr","code":"en","status":1,"default":true}]');
        Helpers::insert_business_settings_key('language','["en"]');
        //version 2.0.1
        // Helpers::insert_business_settings_key('otp_interval_time', '30');
        // Helpers::insert_business_settings_key('max_otp_hit', '5');

        Helpers::insert_business_settings_key("home_delivery_status" , "1");
        Helpers::insert_business_settings_key("takeaway_status" , "1");

        //version 2.2.0
        Helpers::insert_data_settings_key('admin_login_url', 'login_admin' ,'admin');
        Helpers::insert_data_settings_key('admin_employee_login_url', 'login_admin_employee' ,'admin-employee');
        Helpers::insert_data_settings_key('store_login_url', 'login_store' ,'vendor');
        Helpers::insert_data_settings_key('store_employee_login_url', 'login_store_employee' ,'vendor-employee');

        Helpers::insert_business_settings_key('subscription_business_model', '0');
        Helpers::insert_business_settings_key('commission_business_model', '1');
        Helpers::insert_business_settings_key('subscription_deadline_warning_days', '7');
        Helpers::insert_business_settings_key('subscription_free_trial_days', '7');
        Helpers::insert_business_settings_key('subscription_free_trial_type', 'day');
        Helpers::insert_business_settings_key('subscription_free_trial_status', '1');
        Helpers::insert_business_settings_key('subscription_usage_max_time', '80');

        Helpers::insert_business_settings_key('check_daily_subscription_validity_check', date('Y-m-d'));

        $landing = \App\Models\BusinessSetting::where('key', 'landing_page')->exists();
        if(!$landing){
            Helpers::insert_business_settings_key('landing_page','1');
            Helpers::insert_business_settings_key('landing_integration_type','none');
        }
        Helpers::insert_business_settings_key("dm_max_cash_in_hand" , "5000");

        if(\App\Models\NotificationSetting::count() == 0 ){
            Helpers::notificationDataSetup();
        }
        Helpers::updateAdminNotificationSetupDataSetup();
        Helpers::addNewAdminNotificationSetupDataSetup();

        Helpers::insert_business_settings_key('country_picker_status', '1');
        Helpers::insert_business_settings_key('manual_login_status', '1');
    }
}