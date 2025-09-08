<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\Store;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorSelfRegistration;
use App\Mail\StoreRegistration;
use App\Models\Admin;
use Modules\Rental\Emails\ProviderRegistration;
use Modules\Rental\Emails\ProviderSelfRegistration;

class VendorRegistrationService
{
    public function createVendorAndStore($request)
    {
        $vendor = $this->createVendor($request);
        $store = $this->createStore($request, $vendor);

        $this->sendRegistrationEmails($request, $vendor, $store);

        return $store;
    }

    private function createVendor($request)
    {
        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->status = null;
        $vendor->save();

        return $vendor;
    }

    private function createStore($request, $vendor)
    {
        $store = new Store;
        $store->name =  $request->name[array_search('default', $request->lang)];
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        $store->address = $request->address[array_search('default', $request->lang)];
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        $store->module_id = $request->module_id;
        $store->pickup_zone_id = json_encode($request['pickup_zone_id']?? []) ;
        $store->tax = $request->tax;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->status = 0;
        $store->store_business_model = 'none';
        $store->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Store', data_id: $store->id, data_value: $store->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'address', name_field: 'address', model_name: 'Store', data_id: $store->id, data_value: $store->address);

        return $store;
    }

    private function sendRegistrationEmails($request, $vendor, $store)
    {
        try{
            $admin= Admin::where('role_id', 1)->first();
            if($store->module->module_type != 'rental' && config('mail.status') && Helpers::get_mail_status('registration_mail_status_store') == '1' &&  Helpers::getNotificationStatusData('store','store_registration','mail_status') ){
                Mail::to($request['email'])->send(new VendorSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
            elseif($store->module->module_type == 'rental' && addon_published_status('Rental')&& config('mail.status') && Helpers::get_mail_status('rental_registration_mail_status_provider') == '1' &&  Helpers::getRentalNotificationStatusData('provider','provider_registration','mail_status') ){
                Mail::to($request['email'])->send(new ProviderSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }

            if($store->module->module_type != 'rental' && config('mail.status') && Helpers::get_mail_status('store_registration_mail_status_admin') == '1' &&  Helpers::getNotificationStatusData('admin','store_self_registration','mail_status') ){
                Mail::to($admin['email'])->send(new StoreRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            } elseif($store->module->module_type == 'rental' && addon_published_status('Rental')&& config('mail.status') && Helpers::get_mail_status('rental_provider_registration_mail_status_admin') == '1' &&  Helpers::getRentalNotificationStatusData('admin','provider_self_registration','mail_status') ){
                Mail::to($admin['email'])->send(new ProviderRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }

        }catch(\Exception $ex){
            info($ex->getMessage());
        }
    }
}