<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Order;
use App\Models\Contact;
use App\Models\DataSetting;
use App\Models\AdminFeature;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\AdminTestimonial;
use Gregwar\Captcha\CaptchaBuilder;
use App\Models\AdminSpecialCriteria;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use App\Models\AdminPromotionalBanner;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function handle_landing_page_redirection($view, $data = [])
    {
        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if (isset($config) && $config) {
            return view($view, $data);
        } elseif ($landing_integration_type == 'file_upload' && File::exists('resources/views/layouts/landing/custom/index.blade.php')) {
            return view('layouts.landing.custom.index');
        } elseif ($landing_integration_type == 'url') {
            return redirect($redirect_url);
        } else {
            abort(404);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $datas = DataSetting::with('translations', 'storage')->where('type', 'admin_landing_page')->get();
        $settings = $this->format_settings($datas);

        $landing_data = $this->get_landing_page_data($settings);
        $new_user = request()?->new_user ?? null;

        return $this->handle_landing_page_redirection('home', compact('landing_data', 'new_user'));
    }

    private function format_settings($datas)
    {
        $settings = [];
        foreach ($datas as $value) {
            $key = $value->key;
            $settings[$key] = optional($value->translations->first())->value ?? $value->value;
            $settings["{$key}_storage"] = optional($value->storage->first())->value ?? 'public';
        }
        return $settings;
    }

    private function get_landing_page_data($settings)
    {
        $opening_time = BusinessSetting::where('key', 'opening_time')->first();
        $closing_time = BusinessSetting::where('key', 'closing_time')->first();
        $opening_day = BusinessSetting::where('key', 'opening_day')->first();
        $closing_day = BusinessSetting::where('key', 'closing_day')->first();
        $promotional_banners = AdminPromotionalBanner::where('status', 1)->get()->toArray();
        $features = AdminFeature::where('status', 1)->get()->toArray();
        $criterias = AdminSpecialCriteria::where('status', 1)->get();
        $testimonials = AdminTestimonial::where('status', 1)->get();

        $zones = Zone::where('status', 1)->get();
        $zones = self::zone_format($zones);

        return [
            'fixed_header_title' => $settings['fixed_header_title'] ?? null,
            'fixed_header_sub_title' => $settings['fixed_header_sub_title'] ?? null,
            'fixed_module_title' => $settings['fixed_module_title'] ?? null,
            'fixed_module_sub_title' => $settings['fixed_module_sub_title'] ?? null,
            'fixed_referal_title' => $settings['fixed_referal_title'] ?? null,
            'fixed_referal_sub_title' => $settings['fixed_referal_sub_title'] ?? null,
            'fixed_newsletter_title' => $settings['fixed_newsletter_title'] ?? null,
            'fixed_newsletter_sub_title' => $settings['fixed_newsletter_sub_title'] ?? null,
            'fixed_footer_article_title' => $settings['fixed_footer_article_title'] ?? null,
            'feature_title' => $settings['feature_title'] ?? null,
            'feature_short_description' => $settings['feature_short_description'] ?? null,
            'earning_title' => $settings['earning_title'] ?? null,
            'earning_sub_title' => $settings['earning_sub_title'] ?? null,
            'earning_seller_image' => $settings['earning_seller_image'] ?? null,
            'earning_seller_image_storage' => $settings['earning_seller_image_storage'] ?? 'public',
            'earning_delivery_image' => $settings['earning_delivery_image'] ?? null,
            'earning_delivery_image_storage' => $settings['earning_delivery_image_storage'] ?? 'public',
            'why_choose_title' => $settings['why_choose_title'] ?? null,
            'download_user_app_title' => $settings['download_user_app_title'] ?? null,
            'download_user_app_sub_title' => $settings['download_user_app_sub_title'] ?? null,
            'download_user_app_image' => $settings['download_user_app_image'] ?? null,
            'download_user_app_image_storage' => $settings['download_user_app_image_storage'] ?? 'public',
            'testimonial_title' => $settings['testimonial_title'] ?? null,
            'contact_us_title' => $settings['contact_us_title'] ?? null,
            'contact_us_sub_title' => $settings['contact_us_sub_title'] ?? null,
            'contact_us_image' => $settings['contact_us_image'] ?? null,
            'contact_us_image_storage' => $settings['contact_us_image_storage'] ?? 'public',
            'opening_time' => $opening_time ? $opening_time->value : null,
            'closing_time' => $closing_time ? $closing_time->value : null,
            'opening_day' => $opening_day ? $opening_day->value : null,
            'closing_day' => $closing_day ? $closing_day->value : null,
            'promotional_banners' => $promotional_banners,
            'features' => $features,
            'criterias' => $criterias,
            'testimonials' => $testimonials,
            'counter_section' => isset($settings['counter_section']) ? json_decode($settings['counter_section'], true) : null,
            'seller_app_earning_links' => isset($settings['seller_app_earning_links']) ? json_decode($settings['seller_app_earning_links'], true) : null,
            'dm_app_earning_links' => isset($settings['dm_app_earning_links']) ? json_decode($settings['dm_app_earning_links'], true) : null,
            'download_user_app_links' => isset($settings['download_user_app_links']) ? json_decode($settings['download_user_app_links'], true) : null,
            'fixed_link' => isset($settings['fixed_link']) ? json_decode($settings['fixed_link'], true) : null,
            'available_zone_status' => (int)($settings['available_zone_status'] ?? 0),
            'available_zone_title' => $settings['available_zone_title'] ?? null,
            'available_zone_short_description' => $settings['available_zone_short_description'] ?? null,
            'available_zone_image' => $settings['available_zone_image'] ?? null,
            'available_zone_image_full_url' => Helpers::get_full_url('available_zone_image', $settings['available_zone_image'] ?? null, $settings['available_zone_image_storage'] ?? 'public'),
            'available_zone_list' => $zones,
        ];
    }

    private function zone_format($data)
    {
        $storage = [];
        foreach ($data as $item) {
            $storage[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'display_name' => $item['display_name'] ? $item['display_name'] : $item['name'],
                'modules' => $item->modules->pluck('module_name')
            ];
        }
        $data = $storage;

        return $data;
    }

    /**
     * Display the terms and conditions page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function terms_and_conditions(Request $request)
    {
        $data = self::get_settings('terms_and_conditions');
        return $this->handle_landing_page_redirection('terms-and-conditions', compact('data'));
    }

    /**
     * Display the about us page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function about_us(Request $request)
    {
        $data = self::get_settings('about_us');
        $data_title = self::get_settings('about_title');
        return $this->handle_landing_page_redirection('about-us', compact('data', 'data_title'));
    }

    /**
     * Display the contact us page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contact_us()
    {
        $custome_recaptcha = new CaptchaBuilder;
        $custome_recaptcha->build();
        Session::put('six_captcha', $custome_recaptcha->getPhrase());
        return $this->handle_landing_page_redirection('contact-us', compact('custome_recaptcha'));
    }

    /**
     * Handle the contact us form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send_message(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        $recaptcha = Helpers::get_business_settings('recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) {
                        $secret_key = Helpers::get_business_settings('recaptcha')['secret_key'];
                        $gResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                            'secret' => $secret_key,
                            'response' => $value,
                            'remoteip' => \request()->ip(),
                        ]);

                        if (!$gResponse->successful()) {
                            $fail(translate('ReCaptcha Failed'));
                        }
                    },
                ],
            ]);
        } else if (strtolower(session('six_captcha')) != strtolower($request->custome_recaptcha)) {
            Toastr::error(translate('messages.ReCAPTCHA Failed'));
            return back();
        }

        $contact = new Contact;
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->subject = $request->subject;
        $contact->message = $request->message;
        $contact->save();

        Toastr::success('Message sent successfully!');
        return back();
    }

    /**
     * Display the privacy policy page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function privacy_policy(Request $request)
    {
        $data = self::get_settings('privacy_policy');
        return $this->handle_landing_page_redirection('privacy-policy', compact('data'));
    }

    /**
     * Display the refund policy page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function refund_policy(Request $request)
    {
        $data = self::get_settings('refund_policy');
        $status = self::get_settings_status('refund_policy_status');
        abort_if($status == 0, 404);
        return $this->handle_landing_page_redirection('refund', compact('data'));
    }

    /**
     * Display the shipping policy page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function shipping_policy(Request $request)
    {
        $data = self::get_settings('shipping_policy');
        $status = self::get_settings_status('shipping_policy_status');
        abort_if($status == 0, 404);
        return $this->handle_landing_page_redirection('shipping-policy', compact('data'));
    }

    /**
     * Display the cancelation policy page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function cancelation(Request $request)
    {
        $data = self::get_settings('cancellation_policy');
        $status = self::get_settings_status('cancellation_policy_status');
        abort_if($status == 0, 404);
        return $this->handle_landing_page_redirection('cancelation', compact('data'));
    }

    public static function get_settings($name)
    {
        $config = null;
        $data = DataSetting::where(['key' => $name])->first();
        return $data ? $data->value : '';
    }

    public static function get_settings_localization($name, $lang)
    {
        $data = DataSetting::withoutGlobalScope('translate')->with(['translations' => function ($query) use ($lang) {
            return $query->where('locale', $lang);
        }])->where(['key' => $name])->first();
        if ($data && count($data->translations ?? []) > 0) {
            $data = $data->translations[0]['value'];
        } else {
            $data = $data ? $data->value : '';
        }
        return $data;
    }

    public static function get_settings_status($name)
    {
        $data = DataSetting::where(['key' => $name])->first()?->value;
        return $data;
    }

    /**
     * Set the language for the landing page.
     *
     * @param  string  $local
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lang($local)
    {
        $direction = BusinessSetting::where('key', 'site_direction')->first();
        $direction = $direction->value ?? 'ltr';
        $language = BusinessSetting::where('key', 'system_language')->first();
        foreach (json_decode($language['value'], true) as $key => $data) {
            if ($data['code'] == $local) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }
        session()->forget('landing_language_settings');
        Helpers::landing_language_load();
        session()->put('landing_site_direction', $direction);
        session()->put('landing_local', $local);
        return redirect()->back();
    }


    /**
     * Generate a subscription invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscription_invoice($id)
    {
        $id = base64_decode($id);
        $BusinessData = ['admin_commission', 'business_name', 'address', 'phone', 'logo', 'email_address'];
        $transaction = SubscriptionTransaction::with(['store.vendor', 'package:id,package_name,price'])->findOrFail($id);
        $BusinessData = BusinessSetting::whereIn('key', $BusinessData)->pluck('value', 'key');
        $logo = BusinessSetting::where('key', "logo")->first();
        $mpdf_view = View::make('subscription-invoice', compact('transaction', 'BusinessData', 'logo'));
        Helpers::gen_mpdf(view: $mpdf_view, file_prefix: 'Subscription', file_postfix: $id);
        return back();
    }
    /**
     * Generate an order invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function order_invoice($id)
    {
        $id = base64_decode($id);
        $BusinessData = ['footer_text', 'email_address'];
        $order = Order::findOrFail($id);
        $BusinessData = BusinessSetting::whereIn('key', $BusinessData)->pluck('value', 'key');
        $logo = BusinessSetting::where('key', "logo")->first();
        $mpdf_view = View::make('order-invoice', compact('order', 'BusinessData', 'logo'));
        Helpers::gen_mpdf(view: $mpdf_view, file_prefix: 'OrderInvoice', file_postfix: $id);
        return back();
    }
}
