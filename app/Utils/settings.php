<?php

use App\Models\BusinessSetting;


if (!function_exists('getWebConfig')) {
    function getWebConfig($name):string|object|array
    {
        $config = null;
        $check = ['currency_model', 'currency_symbol_position', 'system_default_currency', 'language', 'company_name', 'decimal_point_settings', 'product_brand', 'digital_product', 'company_email'];

        if (in_array($name, $check) && session()->has($name)) {
            $config = session($name);
        } else {
            $data = BusinessSetting::where(['key' => $name])->first();
            if (isset($data)) {
                $config = json_decode($data['value'], true);
                if (is_null($config)) {
                    $config = $data['value'];
                }
            }

            if (in_array($name, $check)) {
                session()->put($name, $config);
            }
        }

        return $config;
    }
    function getWebConfigStatus($name):string|object|array|int
    {
        $config = 0;
        $check = ['currency_model', 'currency_symbol_position', 'system_default_currency', 'language', 'company_name', 'decimal_point_settings', 'product_brand', 'digital_product', 'company_email'];

        if (in_array($name, $check) && session()->has($name)) {
            $config = session($name);
        } else {
            $data = BusinessSetting::where(['key' => $name])->first();
            if (isset($data)) {
                $config = json_decode($data['value'], true);
                if (is_null($config)) {
                    $config = $data['value'];
                }
            }

            if (in_array($name, $check)) {
                session()->put($name, $config);
            }
        }

        return $config;
    }
}
