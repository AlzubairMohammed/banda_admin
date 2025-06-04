<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use App\Models\Zone;
use App\Models\Module;
use App\Models\Currency;
use App\Models\DMVehicle;
use App\Models\DataSetting;
use App\Models\SocialMedia;
use App\Traits\AddonHelper;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\ReactTestimonial;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use Illuminate\Support\Facades\Http;
use App\Models\FlutterSpecialCriteria;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\Rental\Entities\Vehicle;

class ConfigController extends Controller
{
    private $map_api_key;

    use AddonHelper;

    function __construct()
    {
        $map_api_key_server = BusinessSetting::where(['key' => 'map_api_key_server'])->first();
        $map_api_key_server = $map_api_key_server ? $map_api_key_server->value : null;
        $this->map_api_key = $map_api_key_server;
    }

    public function configuration()
    {
        $key = ['currency_code', 'cash_on_delivery', 'digital_payment', 'default_location', 'business_name', 'logo', 'address', 'phone', 'email_address', 'country', 'currency_symbol_position', 'app_minimum_version_android', 'app_url_android', 'app_minimum_version_ios', 'app_url_ios', 'app_url_android_store', 'app_minimum_version_ios_store', 'app_url_ios_store', 'app_minimum_version_ios_deliveryman', 'app_url_ios_deliveryman', 'app_minimum_version_android_deliveryman', 'app_minimum_version_android_store', 'app_url_android_deliveryman', 'customer_verification', 'schedule_order', 'order_delivery_verification', 'per_km_shipping_charge', 'minimum_shipping_charge', 'show_dm_earning', 'canceled_by_deliveryman', 'canceled_by_store', 'timeformat', 'toggle_veg_non_veg', 'toggle_dm_registration', 'toggle_store_registration', 'schedule_order_slot_duration', 'parcel_per_km_shipping_charge', 'parcel_minimum_shipping_charge', 'web_app_landing_page_settings', 'footer_text', 'landing_page_links', 'loyalty_point_exchange_rate', 'loyalty_point_item_purchase_point', 'loyalty_point_status', 'loyalty_point_minimum_point', 'wallet_status', 'dm_tips_status', 'ref_earning_status', 'ref_earning_exchange_rate', 'refund_active_status', 'refund', 'cancelation', 'shipping_policy', 'prescription_order_status', 'tax_included', 'icon', 'cookies_text', 'home_delivery_status', 'takeaway_status', 'additional_charge', 'additional_charge_status', 'additional_charge_name', 'dm_picture_upload_status', 'partial_payment_status', 'partial_payment_method', 'add_fund_status', 'offline_payment_status', 'websocket_url', 'websocket_port', 'websocket_status', 'guest_checkout_status', 'disbursement_type', 'restaurant_disbursement_waiting_time', 'dm_disbursement_waiting_time', 'min_amount_to_pay_store', 'min_amount_to_pay_dm', 'admin_commission',
        'new_customer_discount_status', 'new_customer_discount_amount', 'new_customer_discount_amount_type', 'new_customer_discount_amount_validity', 'new_customer_discount_validity_type', 'store_review_reply', 'subscription_business_model', 'commission_business_model', 'subscription_deadline_warning_days', 'subscription_deadline_warning_message', 'subscription_free_trial_days', 'subscription_free_trial_type', 'subscription_free_trial_status', 'country_picker_status', 'firebase_otp_verification', 'manual_login_status','otp_login_status','social_login_status','google_login_status','facebook_login_status','apple_login_status','email_verification_status','phone_verification_status','admin_free_delivery_option','admin_free_delivery_status','free_delivery_over'
        ];

        $vehicle_distance_min=0;
        $vehicle_hourly_min=0;

        $drivemondExternalSetting = false;


        $cacheKey = 'business_settings_config_keys';
        $settings = Cache::rememberForever($cacheKey, function () use ($key) {
            return array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');
        });
        $image_key = ['logo', 'icon', 'web_app_landing_page_settings'];
        $data = [];

        foreach ($image_key as $value) {
            $data[$value . '_storage'] = Cache::rememberForever("business_settings_config_{$value}_storage", function () use ($value) {
                return BusinessSetting::where('key', $value)->first()?->storage[0]?->value ?? 'public';
            });
        }

        $DataSetting = Cache::rememberForever("data_settings_flutter_landing_page", function () {
            return DataSetting::where('type', 'flutter_landing_page')
                ->where('key', 'download_user_app_links')
                ->pluck('value', 'key')
                ->toArray();
        });
        $DataSetting = isset($DataSetting['download_user_app_links']) ? json_decode($DataSetting['download_user_app_links'], true) : [];
        $landing_page_links = isset($settings['landing_page_links']) ? json_decode($settings['landing_page_links'], true) : [];
        $landing_page_links['app_url_android_status'] = data_get($DataSetting, 'playstore_url_status', null);
        $landing_page_links['app_url_android'] = data_get($DataSetting, 'playstore_url', null);
        $landing_page_links['app_url_ios_status'] = data_get($DataSetting, 'apple_store_url_status', null);
        $landing_page_links['app_url_ios'] = data_get($DataSetting, 'apple_store_url', null);

        $currency_symbol = Cache::rememberForever("business_settings_currency_symbol", function () {
            return Currency::where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        });
        $cod = json_decode($settings['cash_on_delivery'], true);
        $digital_payment = json_decode($settings['digital_payment'], true);
        $default_location = isset($settings['default_location']) ? json_decode($settings['default_location'], true) : 0;

        $admin_free_delivery=[
            'status'=>(boolean) data_get($settings,'admin_free_delivery_status',0),
            'type'=>data_get($settings,'admin_free_delivery_option'),
            'free_delivery_over'=>(float) data_get($settings,'free_delivery_over',0)
        ];


        $additional_charge = isset($settings['additional_charge']) ? (float)$settings['additional_charge'] : 0;
        $module = Cache::rememberForever("module_config", function () {
            return Module::active()->count() == 1 ? Module::active()->first() : null;
        });
        $languages = Helpers::get_business_settings('language');
        $lang_array = [];
        foreach ($languages as $language) {
            array_push($lang_array, [
                'key' => $language,
                'value' => Helpers::get_language_name($language)
            ]);
        }
        $system_languages = Helpers::get_business_settings('system_language');
        $sys_lang_array = [];
        foreach ($system_languages as $language) {
            array_push($sys_lang_array, [
                'key' => $language['code'],
                'value' => Helpers::get_language_name($language['code']),
                'direction' => $language['direction'],
                'default' => $language['default']
            ]);
        }
        $social_login = [];
        foreach (Helpers::get_business_settings('social_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (boolean)$social['status']
            ];
            array_push($social_login, $config);
        }
        $apple_login = [];
        $apples = Helpers::get_business_settings('apple_login');
        if (isset($apples)) {
            foreach (Helpers::get_business_settings('apple_login') as $apple) {
                $config = [
                    'login_medium' => $apple['login_medium'],
                    'status' => (boolean)$apple['status'],
                    'client_id' => $apple['client_id'],
                    'client_id_app' => $apple['client_id_app']??'',
                    'redirect_url_flutter' => $apple['redirect_url_flutter']??'',
                    'redirect_url_react' => $apple['redirect_url_react']??'',
                ];
                array_push($apple_login, $config);
            }
        }

        //addon settings publish status
        $published_status = 0; // Set a default value
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $active_addon_payment_lists = $published_status == 1 ? $this->getPaymentMethods() : $this->getDefaultPaymentMethods();

        $digital_payment_infos = array(
            'digital_payment' => (boolean)($digital_payment['status'] == 1 ? true : false),
            'plugin_payment_gateways' => (boolean)($published_status ? true : false),
            'default_payment_gateways' => (boolean)($published_status ? false : true)
        );

        if (data_get($settings, 'subscription_free_trial_type') == 'year') {
            $trial_period = data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days') / 365 : 0;
        } else if (data_get($settings, 'subscription_free_trial_type') == 'month') {
            $trial_period = data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days') / 30 : 0;
        } else {
            $trial_period = data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days') : 0;
        }

        if(addon_published_status('Rental')){
            $cache_dis_key_min = "vehicle_dis_min_price_conf";
            $vehicle_distance_min = Cache::rememberForever($cache_dis_key_min, function () {
                return Vehicle::where('distance_price' ,'>','0')->min('distance_price');
            });
            $cache_hour_key_min = "vehicle_hour_min_price_conf";
            $vehicle_hourly_min = Cache::rememberForever($cache_hour_key_min, function () {
                return Vehicle::where('hourly_price' ,'>','0')->min('hourly_price');
            });
        }
        return response()->json([
            'business_name' => $settings['business_name'],
            'logo' => $settings['logo'],
            'logo_full_url' => Helpers::get_full_url('business', $settings['logo'], $data['logo_storage'] ?? 'public'),
            'address' => $settings['address'],
            'phone' => $settings['phone'],
            'email' => $settings['email_address'],

            'country' => $settings['country'],
            'default_location' => ['lat' => $default_location ? $default_location['lat'] : '23.757989', 'lng' => $default_location ? $default_location['lng'] : '90.360587'],
            'currency_symbol' => $currency_symbol,
            'currency_symbol_direction' => $settings['currency_symbol_position'],
            'app_minimum_version_android' => (float)$settings['app_minimum_version_android'],
            'app_url_android' => $settings['app_url_android'],
            'app_url_ios' => $settings['app_url_ios'],
            'app_minimum_version_ios' => (float)$settings['app_minimum_version_ios'],
            'app_minimum_version_android_store' => (float)(isset($settings['app_minimum_version_android_store']) ? $settings['app_minimum_version_android_store'] : 0),
            'app_url_android_store' => (isset($settings['app_url_android_store']) ? $settings['app_url_android_store'] : null),
            'app_minimum_version_ios_store' => (float)(isset($settings['app_minimum_version_ios_store']) ? $settings['app_minimum_version_ios_store'] : 0),
            'app_url_ios_store' => (isset($settings['app_url_ios_store']) ? $settings['app_url_ios_store'] : null),
            'app_minimum_version_android_deliveryman' => (float)(isset($settings['app_minimum_version_android_deliveryman']) ? $settings['app_minimum_version_android_deliveryman'] : 0),
            'app_url_android_deliveryman' => (isset($settings['app_url_android_deliveryman']) ? $settings['app_url_android_deliveryman'] : null),
            'app_minimum_version_ios_deliveryman' => (float)(isset($settings['app_minimum_version_ios_deliveryman']) ? $settings['app_minimum_version_ios_deliveryman'] : 0),
            'app_url_ios_deliveryman' => (isset($settings['app_url_ios_deliveryman']) ? $settings['app_url_ios_deliveryman'] : null),
            'customer_verification' => (boolean)$settings['customer_verification'],
            'prescription_order_status' => isset($settings['prescription_order_status']) ? (boolean)$settings['prescription_order_status'] : false,
            'schedule_order' => (boolean)$settings['schedule_order'],
            'order_delivery_verification' => (boolean)$settings['order_delivery_verification'],
            'cash_on_delivery' => (boolean)($cod['status'] == 1 ? true : false),
            'digital_payment' => (boolean)($digital_payment['status'] == 1 ? true : false),
            'digital_payment_info' => $digital_payment_infos,
            'per_km_shipping_charge' => (double)$settings['per_km_shipping_charge'],
            'minimum_shipping_charge' => (double)$settings['minimum_shipping_charge'],
            'demo' => (boolean)(env('APP_MODE') == 'demo' ? true : false),
            'maintenance_mode' => (boolean)Helpers::get_business_settings('maintenance_mode') ?? 0,
            'order_confirmation_model' => config('order_confirmation_model'),
            'show_dm_earning' => (boolean)$settings['show_dm_earning'],
            'canceled_by_deliveryman' => (boolean)$settings['canceled_by_deliveryman'],
            'canceled_by_store' => (boolean)$settings['canceled_by_store'],
            'timeformat' => (string)$settings['timeformat'],
            'language' => $lang_array,
            'sys_language' => $sys_lang_array,
            'social_login' => $social_login,
            'apple_login' => $apple_login,
            'toggle_veg_non_veg' => (boolean)$settings['toggle_veg_non_veg'],
            'toggle_dm_registration' => (boolean)$settings['toggle_dm_registration'],
            'toggle_store_registration' => (boolean)$settings['toggle_store_registration'],
            'refund_active_status' => (boolean)$settings['refund_active_status'],
            'schedule_order_slot_duration' => (int)$settings['schedule_order_slot_duration'],
            'digit_after_decimal_point' => (int)config('round_up_to_digit'),
            'module_config' => config('module'),
            'module' => $module,
            'parcel_per_km_shipping_charge' => (float)$settings['parcel_per_km_shipping_charge'],
            'parcel_minimum_shipping_charge' => (float)$settings['parcel_minimum_shipping_charge'],
            'social_media' => SocialMedia::active()->get()->toArray(),
            'footer_text' => isset($settings['footer_text']) ? $settings['footer_text'] : '',
            'cookies_text' => isset($settings['cookies_text']) ? $settings['cookies_text'] : '',
            'fav_icon' => $settings['icon'],
            'fav_icon_full_url' => Helpers::get_full_url('business', $settings['icon'], $data['icon_storage'] ?? 'public'),
            'landing_page_links' => $landing_page_links,
            //Added Business Setting
            'dm_tips_status' => (int)(isset($settings['dm_tips_status']) ? $settings['dm_tips_status'] : 0),
            'loyalty_point_exchange_rate' => (int)(isset($settings['loyalty_point_item_purchase_point']) ? $settings['loyalty_point_exchange_rate'] : 0),
            'loyalty_point_item_purchase_point' => (float)(isset($settings['loyalty_point_item_purchase_point']) ? $settings['loyalty_point_item_purchase_point'] : 0.0),
            'loyalty_point_status' => (int)(isset($settings['loyalty_point_status']) ? $settings['loyalty_point_status'] : 0),
            'customer_wallet_status' => (int)(isset($settings['wallet_status']) ? $settings['wallet_status'] : 0),
            'ref_earning_status' => (int)(isset($settings['ref_earning_status']) ? $settings['ref_earning_status'] : 0),
            'ref_earning_exchange_rate' => (double)(isset($settings['ref_earning_exchange_rate']) ? $settings['ref_earning_exchange_rate'] : 0),
            'refund_policy' => (int)(self::get_settings_status('refund_policy_status')),
            'cancelation_policy' => (int)(self::get_settings_status('cancellation_policy_status')),
            'shipping_policy' => (int)(self::get_settings_status('shipping_policy_status')),
            'loyalty_point_minimum_point' => (int)(isset($settings['loyalty_point_minimum_point']) ? $settings['loyalty_point_minimum_point'] : 0),
            'tax_included' => (int)(isset($settings['tax_included']) ? $settings['tax_included'] : 0),
            'home_delivery_status' => (int)(isset($settings['home_delivery_status']) ? $settings['home_delivery_status'] : 0),
            'takeaway_status' => (int)(isset($settings['takeaway_status']) ? $settings['takeaway_status'] : 0),
            'active_payment_method_list' => $active_addon_payment_lists,
            'additional_charge_status' => (int)(isset($settings['additional_charge_status']) ? $settings['additional_charge_status'] : 0),
            'additional_charge_name' => (isset($settings['additional_charge_name']) ? $settings['additional_charge_name'] : 'Service Charge'),
            'additional_charge' => $additional_charge,
            'partial_payment_status' => (int)(isset($settings['partial_payment_status']) ? $settings['partial_payment_status'] : 0),
            'partial_payment_method' => (isset($settings['partial_payment_method']) ? $settings['partial_payment_method'] : ''),
            'dm_picture_upload_status' => (int)(isset($settings['dm_picture_upload_status']) ? $settings['dm_picture_upload_status'] : 0),
            'add_fund_status' => (int)(isset($settings['add_fund_status']) ? $settings['add_fund_status'] : 0),
            'offline_payment_status' => (int)(isset($settings['offline_payment_status']) ? $settings['offline_payment_status'] : 0),
            'websocket_status' => (int)(isset($settings['websocket_status']) ? $settings['websocket_status'] : 0),
            'websocket_url' => (isset($settings['websocket_url']) ? $settings['websocket_url'] : ''),
            'websocket_port' => (int)(isset($settings['websocket_port']) ? $settings['websocket_port'] : 6001),
            'websocket_key' => env('PUSHER_APP_KEY'),
            'guest_checkout_status' => (int)(isset($settings['guest_checkout_status']) ? $settings['guest_checkout_status'] : 0),
            'disbursement_type' => (string)(isset($settings['disbursement_type']) ? $settings['disbursement_type'] : 'manual'),
            'restaurant_disbursement_waiting_time' => (int)(isset($settings['restaurant_disbursement_waiting_time']) ? $settings['restaurant_disbursement_waiting_time'] : 0),
            'dm_disbursement_waiting_time' => (int)(isset($settings['dm_disbursement_waiting_time']) ? $settings['dm_disbursement_waiting_time'] : 0),
            'min_amount_to_pay_store' => (float)(isset($settings['min_amount_to_pay_store']) ? $settings['min_amount_to_pay_store'] : 0),
            'min_amount_to_pay_dm' => (float)(isset($settings['min_amount_to_pay_dm']) ? $settings['min_amount_to_pay_dm'] : 0),
            'new_customer_discount_status' => (int)(isset($settings['new_customer_discount_status']) ? $settings['new_customer_discount_status'] : 0),
            'new_customer_discount_amount' => (float)(isset($settings['new_customer_discount_amount']) ? $settings['new_customer_discount_amount'] : 0),
            'new_customer_discount_amount_type' => (isset($settings['new_customer_discount_amount_type']) ? $settings['new_customer_discount_amount_type'] : 'amount'),
            'new_customer_discount_amount_validity' => (int)(isset($settings['new_customer_discount_amount_validity']) ? $settings['new_customer_discount_amount_validity'] : 0),
            'new_customer_discount_validity_type' => (isset($settings['new_customer_discount_validity_type']) ? $settings['new_customer_discount_validity_type'] : 'day'),
            'store_review_reply' => (int)(isset($settings['store_review_reply']) ? $settings['store_review_reply'] : 0),
            'admin_commission' => (float)(isset($settings['admin_commission']) ? $settings['admin_commission'] : 0),
            'subscription_business_model' => (int)(isset($settings['subscription_business_model']) ? $settings['subscription_business_model'] : 1),
            'commission_business_model' => (int)(isset($settings['commission_business_model']) ? $settings['commission_business_model'] : 1),
            'subscription_deadline_warning_days' => (int)(isset($settings['subscription_deadline_warning_days']) ? $settings['subscription_deadline_warning_days'] : 1),
            'subscription_deadline_warning_message' => isset($settings['subscription_deadline_warning_message']) ? $settings['subscription_deadline_warning_message'] : null,
            'subscription_free_trial_days' => (int)$trial_period,
            'subscription_free_trial_type' => (isset($settings['subscription_free_trial_type']) ? $settings['subscription_free_trial_type'] : 'day'),
            'subscription_free_trial_status' => (int)(isset($settings['subscription_free_trial_status']) ? $settings['subscription_free_trial_status'] : 0),
            'country_picker_status' => (int)(isset($settings['country_picker_status']) ? $settings['country_picker_status'] : 1),
            'external_system' => $drivemondExternalSetting,
            'drivemond_app_url_android' => $drivemondExternalSetting ? Helpers::get_external_data('drivemond_app_url_android') : '',
            'drivemond_app_url_ios' => $drivemondExternalSetting ? Helpers::get_external_data('drivemond_app_url_ios') : '',
            'firebase_otp_verification' => (int)(isset($settings['firebase_otp_verification']) ? $settings['firebase_otp_verification'] : 0),
            'centralize_login' => [
                'manual_login_status' => (int)(isset($settings['manual_login_status']) ? $settings['manual_login_status'] : 0),
                'otp_login_status' => (int)(isset($settings['otp_login_status']) ? $settings['otp_login_status'] : 0),
                'social_login_status' => (int)(isset($settings['social_login_status']) ? $settings['social_login_status'] : 0),
                'google_login_status' => (int)(isset($settings['google_login_status']) ? $settings['google_login_status'] : 0),
                'facebook_login_status' => (int)(isset($settings['facebook_login_status']) ? $settings['facebook_login_status'] : 0),
                'apple_login_status' => (int)(isset($settings['apple_login_status']) ? $settings['apple_login_status'] : 0),
                'email_verification_status' => (int)(isset($settings['email_verification_status']) ? $settings['email_verification_status'] : 0),
                'phone_verification_status' => (int)(isset($settings['phone_verification_status']) ? $settings['phone_verification_status'] : 0),
            ],

            'vehicle_distance_min' =>(float) $vehicle_distance_min?? 0,
            'vehicle_hourly_min' => (float) $vehicle_hourly_min?? 0,
            'admin_free_delivery' =>$admin_free_delivery,
            'is_sms_active' =>  (boolean)  Setting::whereJsonContains('live_values->status','1')->where('settings_type', 'sms_config')->exists(),
            'is_mail_active' =>  (boolean)config('mail.status'),
        ]);
    }

    public static function get_settings_status($name)
    {
        $data = Cache::rememberForever('data_settings_' . $name, function () use ($name) {
            return DataSetting::where('key', $name)->value('value');
        });

        return $data ?? 0;
    }

    public function get_zone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zones = Zone::with('modules')->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))
            ->selectRaw('zones.*, ABS(ST_Area(coordinates)) as area')->orderBy('area', 'asc')->latest()->get(['id', 'status', 'cash_on_delivery', 'digital_payment', 'offline_payment', 'increased_delivery_fee', 'increased_delivery_fee_status', 'increase_delivery_charge_message']);
        if (count($zones) < 1) {
            return response()->json([
                'errors' => [
                    ['code' => 'coordinates', 'message' => translate('messages.service_not_available_in_this_area')]
                ]
            ], 404);
        }
        $data = array_filter($zones->toArray(), function ($zone) {
            if ($zone['status'] == 1) {
                return $zone;
            }
        });

        if (count($data) > 0) {
            return response()->json(['zone_id' => json_encode(array_column($data, 'id')), 'zone_data' => array_values($data)], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'coordinates', 'message' => translate('messages.we_are_temporarily_unavailable_in_this_area')]
            ]
        ], 403);
    }

    public function place_api_autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $apiKey = $this->map_api_key;
        $url = "https://places.googleapis.com/v1/places:autocomplete";
        $data = [
            "input" => $request['search_text'],
            "languageCode" => app()->getLocale(),
        ];

        $headers = [
            "Content-Type: application/json",
            "X-Goog-Api-Key: $apiKey",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response,true);
    }


    public function distance_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
            'mode' => 'nullable|in:DRIVE,WALK',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $apiKey = $this->map_api_key;
        $url = "https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix";

        $data = [
            "origins" => [
                ["waypoint" => ["location" => ["latLng" => ["latitude" => $request['origin_lat'], "longitude" => $request['origin_lng']]]]]
            ],
            "destinations" => [
                ["waypoint" => ["location" => ["latLng" => ["latitude" => $request['destination_lat'], "longitude" => $request['destination_lng']]]]],
            ],
            "travelMode" =>  $request['mode'] ?? 'WALK',
           // "routingPreference" => "TRAFFIC_AWARE"
        ];

        $headers = [
            "Content-Type: application/json",
            "X-Goog-Api-Key: $apiKey",
            "X-Goog-FieldMask: duration,distanceMeters,localizedValues"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true)[0];
    }


    public function place_api_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $apiKey = $this->map_api_key;
        $url = 'https://places.googleapis.com/v1/places/'.$request['placeid'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $apiKey,
            'X-Goog-FieldMask: id,displayName,formattedAddress,location',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response,true);
    }

    public function geocode_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $this->map_api_key);
        return $response->json();
    }

    public function landing_page()
    {
        $key = ['react_header_banner', 'banner_section_full', 'banner_section_half', 'footer_logo', 'app_section_image',
            'react_feature', 'app_download_button', 'discount_banner', 'landing_page_links', 'delivery_service_section', 'hero_section', 'download_app_section', 'landing_page_text'];
        $settings = array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');
        return response()->json(
            [
                'react_header_banner' => (isset($settings['react_header_banner'])) ? $settings['react_header_banner'] : null,
                'app_section_image' => (isset($settings['app_section_image'])) ? $settings['app_section_image'] : null,
                'footer_logo' => (isset($settings['footer_logo'])) ? $settings['footer_logo'] : null,
                'banner_section_full' => (isset($settings['banner_section_full'])) ? json_decode($settings['banner_section_full'], true) : null,
                'banner_section_half' => (isset($settings['banner_section_half'])) ? json_decode($settings['banner_section_half'], true) : [],
                'react_feature' => (isset($settings['react_feature'])) ? json_decode($settings['react_feature'], true) : [],
                'app_download_button' => (isset($settings['app_download_button'])) ? json_decode($settings['app_download_button'], true) : [],
                'discount_banner' => (isset($settings['discount_banner'])) ? json_decode($settings['discount_banner'], true) : null,
                'landing_page_links' => (isset($settings['landing_page_links'])) ? json_decode($settings['landing_page_links'], true) : null,
                'hero_section' => (isset($settings['hero_section'])) ? json_decode($settings['hero_section'], true) : null,
                'delivery_service_section' => (isset($settings['delivery_service_section'])) ? json_decode($settings['delivery_service_section'], true) : null,
                'download_app_section' => (isset($settings['download_app_section'])) ? json_decode($settings['download_app_section'], true) : null,
                'landing_page_text' => (isset($settings['landing_page_text'])) ? json_decode($settings['landing_page_text'], true) : null,
            ]);
    }

    public function extra_charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distance' => 'required',
        ]);
        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $distance_data = $request->distance ?? 1;
        $data = DmVehicle::active()
            ->where(function ($query) use ($distance_data) {
                $query->where('starting_coverage_area', '<=', $distance_data)->where('maximum_coverage_area', '>=', $distance_data)
                    ->orWhere(function ($query) use ($distance_data) {
                        $query->where('starting_coverage_area', '>=', $distance_data);
                    });
            })->orderBy('starting_coverage_area')->first();

        $extra_charges = (float)(isset($data) ? $data->extra_charges : 0);
        return response()->json($extra_charges, 200);
    }

    public function get_vehicles(Request $request)
    {
        $data = DMVehicle::active()->get(['id', 'type']);
        return response()->json($data, 200);
    }

    public function react_landing_page()
    {
        $datas = DataSetting::with('translations')->whereIn('type', ['react_landing_page' ,'module_home_page_data','module_vendor_registration_data'])->get();
        $data = [];
        foreach ($datas as $key => $value) {
            if (count($value->translations) > 0) {
                $cred = [
                    $value->key => $value->translations[0]['value'],
                ];
                array_push($data, $cred);
            } else {
                $cred = [
                    $value->key => $value->value,
                ];
                array_push($data, $cred);
            }
            if (isset($value->storage)) {

                $cred = [
                    $value->key . '_storage' => $value?->storage[0]?->value ?? 'public',
                ];
                array_push($data, $cred);
            }
        }
        $settings = [];
        foreach ($data as $single_data) {
            foreach ($single_data as $key => $single_value) {
                $settings[$key] = $single_value;
            }
        }

        $reviews = ReactTestimonial::get();

        $awsUrl = config('filesystems.disks.s3.url');
        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsBaseURL = rtrim($awsUrl, '/') . '/' . ltrim($awsBucket . '/');

        $promotional_banners = [];
        foreach (json_decode($settings['promotion_banner'], true) as $value) {
            $promotional_banners[] = Helpers::get_full_url('promotional_banner', $value['img'], $value['storage'] ?? 'public');
        }

        $zones= Zone::where('status',1)->get();
        $zones = self::zone_format($zones);

        return response()->json(
            [
                'base_urls' => [
                    'header_icon_url' => asset('storage/app/public/header_icon'),
                    'header_banner_url' => asset('storage/app/public/header_banner'),
                    'testimonial_image_url' => asset('storage/app/public/reviewer_image'),
                    'promotional_banner_url' => asset('storage/app/public/promotional_banner'),
                    'business_image_url' => asset('storage/app/public/business_image'),
                ],
                's3_base_urls' => [
                    'header_icon_url' => $awsBaseURL . 'header_icon',
                    'header_banner_url' => $awsBaseURL . 'header_banner',
                    'testimonial_image_url' => $awsBaseURL . 'reviewer_image',
                    'promotional_banner_url' => $awsBaseURL . 'promotional_banner',
                    'business_image_url' => $awsBaseURL . 'business_image',
                ],

                'header_title' => (isset($settings['header_title'])) ? $settings['header_title'] : null,
                'header_sub_title' => (isset($settings['header_sub_title'])) ? $settings['header_sub_title'] : null,
                'header_tag_line' => (isset($settings['header_tag_line'])) ? $settings['header_tag_line'] : null,
                'header_icon' => (isset($settings['header_icon'])) ? $settings['header_icon'] : null,
                'header_icon_full_url' => Helpers::get_full_url('header_icon', (isset($settings['header_icon'])) ? $settings['header_icon'] : null, isset($settings['header_icon_storage']) ? $settings['header_icon_storage'] : 'public'),
                'header_banner' => (isset($settings['header_banner'])) ? $settings['header_banner'] : null,
                'header_banner_full_url' => Helpers::get_full_url('header_banner', (isset($settings['header_banner'])) ? $settings['header_banner'] : null, isset($settings['header_banner_storage']) ? $settings['header_banner_storage'] : 'public'),
                'company_title' => (isset($settings['company_title'])) ? $settings['company_title'] : null,
                'company_sub_title' => (isset($settings['company_sub_title'])) ? $settings['company_sub_title'] : null,
                'company_description' => (isset($settings['company_description'])) ? $settings['company_description'] : null,
                'company_button_name' => (isset($settings['company_button_name'])) ? $settings['company_button_name'] : null,
                'company_button_url' => (isset($settings['company_button_url'])) ? $settings['company_button_url'] : null,
                'download_user_app_title' => (isset($settings['download_user_app_title'])) ? $settings['download_user_app_title'] : null,
                'download_user_app_sub_title' => (isset($settings['download_user_app_sub_title'])) ? $settings['download_user_app_sub_title'] : null,
                'earning_title' => (isset($settings['earning_title'])) ? $settings['earning_title'] : null,
                'earning_sub_title' => (isset($settings['earning_sub_title'])) ? $settings['earning_sub_title'] : null,
                'earning_seller_title' => (isset($settings['earning_seller_title'])) ? $settings['earning_seller_title'] : null,
                'earning_seller_sub_title' => (isset($settings['earning_seller_sub_title'])) ? $settings['earning_seller_sub_title'] : null,
                'earning_seller_button_name' => (isset($settings['earning_seller_button_name'])) ? $settings['earning_seller_button_name'] : null,
                'earning_seller_status' => (int)((isset($settings['join_seller_react_status'])) ? $settings['join_seller_react_status'] : 0),
                'earning_dm_title' => (isset($settings['earning_dm_title'])) ? $settings['earning_dm_title'] : null,
                'earning_dm_sub_title' => (isset($settings['earning_dm_sub_title'])) ? $settings['earning_dm_sub_title'] : null,
                'earning_dm_button_name' => (isset($settings['earning_dm_button_name'])) ? $settings['earning_dm_button_name'] : null,
                'earning_dm_status' => (int)((isset($settings['join_DM_react_status'])) ? $settings['join_DM_react_status'] : 0),

                'business_title' => (isset($settings['business_title'])) ? $settings['business_title'] : null,
                'business_sub_title' => (isset($settings['business_sub_title'])) ? $settings['business_sub_title'] : null,
                'business_image' => (isset($settings['business_image'])) ? $settings['business_image'] : null,
                'business_image_full_url' => Helpers::get_full_url('business_image', isset($settings['business_image']) ? $settings['business_image'] : null, isset($settings['business_image_storage']) ? $settings['business_image_storage'] : 'public'),
                'testimonial_title' => (isset($settings['testimonial_title'])) ? $settings['testimonial_title'] : null,
                'testimonial_list' => (isset($reviews)) ? $reviews : null,
                'fixed_newsletter_title' => (isset($settings['fixed_newsletter_title'])) ? $settings['fixed_newsletter_title'] : null,
                'fixed_newsletter_sub_title' => (isset($settings['fixed_newsletter_sub_title'])) ? $settings['fixed_newsletter_sub_title'] : null,
                'fixed_footer_description' => (isset($settings['fixed_footer_description'])) ? $settings['fixed_footer_description'] : null,
                'fixed_promotional_banner' => (isset($settings['fixed_promotional_banner'])) ? $settings['fixed_promotional_banner'] : null,
                'fixed_promotional_banner_full_url' => Helpers::get_full_url('promotional_banner', (isset($settings['fixed_promotional_banner'])) ? $settings['fixed_promotional_banner'] : null, (isset($settings['fixed_promotional_banner_storage'])) ? $settings['fixed_promotional_banner_storage'] : 'public'),

                'promotion_banners' => (isset($settings['promotion_banner'])) ? json_decode($settings['promotion_banner'], true) : null,
                'promotion_banners_full_url' => $promotional_banners,
                'download_user_app_links' => (isset($settings['download_user_app_links'])) ? json_decode($settings['download_user_app_links'], true) : null,
                'download_business_app_links' => (isset($settings['download_business_app_links'])) ? json_decode($settings['download_business_app_links'], true) : null,

                'available_zone_status' => (int)((isset($settings['available_zone_status'])) ? $settings['available_zone_status'] : 0),
                'available_zone_title' => (isset($settings['available_zone_title'])) ? $settings['available_zone_title'] : null,
                'available_zone_short_description' => (isset($settings['available_zone_short_description'])) ? $settings['available_zone_short_description'] : null,
                'available_zone_image' => (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null,
                'available_zone_image_full_url' => Helpers::get_full_url('available_zone_image', (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null, (isset($settings['available_zone_image_storage'])) ? $settings['available_zone_image_storage'] : 'public'),
                'available_zone_list' => $zones,

                'module_home_page_data_title' => (isset($settings['module_home_page_data_title'])) ? $settings['module_home_page_data_title'] : null,
                'module_home_page_data_sub_title' => (isset($settings['module_home_page_data_sub_title'])) ? $settings['module_home_page_data_sub_title'] : null,
                'module_home_page_data_image' => isset($settings['module_home_page_data_image'])?

                Helpers::get_full_url('react_landing', $settings['module_home_page_data_image']?? '', $settings['module_home_page_data_image_storage']?? 'public','upload_image_1' ) : '',

                'module_vendor_registration_data_title' => (isset($settings['module_vendor_registration_data_title'])) ? $settings['module_vendor_registration_data_title'] : null,
                'module_vendor_registration_data_sub_title' => (isset($settings['module_vendor_registration_data_sub_title'])) ? $settings['module_vendor_registration_data_sub_title'] : null,
                'module_vendor_registration_data_button_title' => (isset($settings['module_vendor_registration_data_button_title'])) ? $settings['module_vendor_registration_data_button_title'] : null,
                'module_vendor_registration_data_image' =>
                isset($settings['module_vendor_registration_data_image'])?
                Helpers::get_full_url('react_landing', $settings['module_vendor_registration_data_image']?? '', $settings['module_vendor_registration_data_image_storage']?? 'public','upload_image_1' ) : '',

            ]);
    }

    public function flutter_landing_page()
    {
        $datas = DataSetting::with('translations')->where('type', 'flutter_landing_page')->get();
        $data = [];
        foreach ($datas as $key => $value) {
            if (count($value->translations) > 0) {
                $cred = [
                    $value->key => $value->translations[0]['value'],
                ];
                array_push($data, $cred);
            } else {
                $cred = [
                    $value->key => $value->value,
                ];
                array_push($data, $cred);
            }
            if (isset($value->storage)) {

                $cred = [
                    $value->key . '_storage' => $value?->storage[0]?->value ?? 'public',
                ];
                array_push($data, $cred);
            }
        }
        $settings = [];
        foreach ($data as $single_data) {
            foreach ($single_data as $key => $single_value) {
                $settings[$key] = $single_value;
            }
        }

        $zones= Zone::where('status',1)->get();
        $zones = self::zone_format($zones);

        $criterias = FlutterSpecialCriteria::where('status', 1)->get();

        $awsUrl = config('filesystems.disks.s3.url');
        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsBaseURL = rtrim($awsUrl, '/') . '/' . ltrim($awsBucket . '/');

        return response()->json(
            [
                'base_urls' => [
                    'fixed_header_image' => asset('storage/app/public/fixed_header_image'),
                    'special_criteria_image' => asset('storage/app/public/special_criteria'),
                    'download_user_app_image' => asset('storage/app/public/download_user_app_image'),
                ],

                's3_base_urls' => [
                    'fixed_header_image' => $awsBaseURL . 'fixed_header_image',
                    'special_criteria_image' => $awsBaseURL . 'special_criteria',
                    'download_user_app_image' => $awsBaseURL . 'download_user_app_image',
                ],

                'fixed_header_title' => (isset($settings['fixed_header_title'])) ? $settings['fixed_header_title'] : null,
                'fixed_header_sub_title' => (isset($settings['fixed_header_sub_title'])) ? $settings['fixed_header_sub_title'] : null,
                'fixed_header_image' => (isset($settings['fixed_header_image'])) ? $settings['fixed_header_image'] : null,
                'fixed_header_image_full_url' => Helpers::get_full_url('fixed_header_image', (isset($settings['fixed_header_image'])) ? $settings['fixed_header_image'] : null, (isset($settings['fixed_header_image_storage'])) ? $settings['fixed_header_image_storage'] : 'public'),
                'fixed_module_title' => (isset($settings['fixed_module_title'])) ? $settings['fixed_module_title'] : null,
                'fixed_module_sub_title' => (isset($settings['fixed_module_sub_title'])) ? $settings['fixed_module_sub_title'] : null,
                'fixed_location_title' => (isset($settings['fixed_location_title'])) ? $settings['fixed_location_title'] : null,
                'join_seller_title' => (isset($settings['join_seller_title'])) ? $settings['join_seller_title'] : null,
                'join_seller_sub_title' => (isset($settings['join_seller_sub_title'])) ? $settings['join_seller_sub_title'] : null,
                'join_seller_button_name' => (isset($settings['join_seller_button_name'])) ? $settings['join_seller_button_name'] : null,
                'join_seller_status' => (int)((isset($settings['join_seller_flutter_status'])) ? $settings['join_seller_flutter_status'] : 0),
                'join_delivery_man_title' => (isset($settings['join_delivery_man_title'])) ? $settings['join_delivery_man_title'] : null,
                'join_delivery_man_sub_title' => (isset($settings['join_delivery_man_sub_title'])) ? $settings['join_delivery_man_sub_title'] : null,
                'join_delivery_man_button_name' => (isset($settings['join_delivery_man_button_name'])) ? $settings['join_delivery_man_button_name'] : null,
                'join_delivery_man_status' => (int)((isset($settings['join_DM_flutter_status'])) ? $settings['join_DM_flutter_status'] : 0),

                'download_user_app_title' => (isset($settings['download_user_app_title'])) ? $settings['download_user_app_title'] : null,
                'download_user_app_sub_title' => (isset($settings['download_user_app_sub_title'])) ? $settings['download_user_app_sub_title'] : null,
                'download_user_app_image' => (isset($settings['download_user_app_image'])) ? $settings['download_user_app_image'] : null,
                'download_user_app_image_full_url' => Helpers::get_full_url('download_user_app_image', (isset($settings['download_user_app_image'])) ? $settings['download_user_app_image'] : null, (isset($settings['download_user_app_image_storage'])) ? $settings['download_user_app_image_storage'] : 'public'),

                'special_criterias' => (isset($criterias)) ? $criterias : null,

                'download_user_app_links' => (isset($settings['download_user_app_links'])) ? json_decode($settings['download_user_app_links'], true) : null,
                'available_zone_status' => (int)((isset($settings['available_zone_status'])) ? $settings['available_zone_status'] : 0),
                'available_zone_title' => (isset($settings['available_zone_title'])) ? $settings['available_zone_title'] : null,
                'available_zone_short_description' => (isset($settings['available_zone_short_description'])) ? $settings['available_zone_short_description'] : null,
                'available_zone_image' => (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null,
                'available_zone_image_full_url' => Helpers::get_full_url('available_zone_image', (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null, (isset($settings['available_zone_image_storage'])) ? $settings['available_zone_image_storage'] : 'public'),
                'available_zone_list' => $zones,
            ]);
    }

    private function getPaymentMethods()
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = Setting::where('is_active', 1)->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = $method->$credentials;
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData && $credentialsData['status'] == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image,
                    'gateway_image_full_url' => Helpers::get_full_url('payment_modules/gateway_image', $additional_data?->gateway_image, $additional_data?->storage ?? 'public')
                ];
            }
        }
        return $data;
    }

    private function getDefaultPaymentMethods()
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = Setting::where('is_active', 1)->whereIn('settings_type', ['payment_config'])->whereIn('key_name', ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paytabs', 'paystack', 'paymob_accept', 'paytm', 'flutterwave', 'liqpay', 'bkash', 'mercadopago'])->get();

        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = $method->$credentials;
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData && $credentialsData['status'] == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image,
                    'gateway_image_full_url' => Helpers::get_full_url('payment_modules/gateway_image', $additional_data?->gateway_image, $additional_data?->storage ?? 'public')
                ];
            }
        }
        return $data;
    }

    public function offline_payment_method_list(Request $request)
    {
        $data = OfflinePaymentMethod::where('status', 1)->get();
        $data = $data->count() > 0 ? $data : null;
        return response()->json($data, 200);
    }

    private function zone_format($data)
    {
        $storage = [];
        foreach ($data as $item) {
            $storage[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'display_name' => $item['display_name']?$item['display_name']:$item['name'],
                'modules' => $item->modules->pluck('module_name')
            ];
        }
        $data = $storage;

        return $data;
    }

    public function direction_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $apiKey = $this->map_api_key;
        $url = "https://routes.googleapis.com/directions/v2:computeRoutes";

        $data = [
            "origin" => [
                "location" => [
                    "latLng" => [
                        "latitude" => $request['origin_lat'],
                        "longitude" => $request['origin_lng']
                    ]
                ]
            ],
            "destination" => [
                "location" => [
                    "latLng" => [
                        "latitude" => $request['destination_lat'],
                        "longitude" => $request['destination_lng']
                    ]
                ]
            ],
            "travelMode" => strtoupper($request['mode'] ?? 'DRIVE'),
            "routingPreference" => "TRAFFIC_AWARE",
        ];

        $headers = [
            "Content-Type: application/json",
            "X-Goog-Api-Key: $apiKey",
            "X-Goog-FieldMask: routes.duration,routes.distanceMeters,routes.polyline.encodedPolyline"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ["error" => $error];
        }
        return json_decode($response, true);
    }
}
