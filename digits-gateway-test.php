<?php
/**
 * فایل تست برای بررسی تنظیمات درگاه Digits
 */

require_once('wp-load.php');

echo "<h1>بررسی تنظیمات درگاه Digits</h1>";

// بررسی تنظیمات عمومی
echo "<h2>تنظیمات عمومی Digits:</h2>";
$general_settings = get_option('digit_general_settings', []);
if (empty($general_settings)) {
    $general_settings = get_option('digits_general_settings', []);
}
echo "<pre>" . print_r($general_settings, true) . "</pre>";

// بررسی تنظیمات OTP
echo "<h2>تنظیمات OTP:</h2>";
$otp_settings = get_option('digit_otp_settings', []);
if (empty($otp_settings)) {
    $otp_settings = get_option('digits_otp_settings', []);
}
echo "<pre>" . print_r($otp_settings, true) . "</pre>";

// بررسی درگاه فعال
echo "<h2>درگاه فعال:</h2>";
$active_gateway = null;
if (isset($general_settings['gateway'])) {
    $active_gateway = $general_settings['gateway'];
    echo "<p style='color: green;'>✅ درگاه فعال: $active_gateway</p>";
} else {
    echo "<p style='color: red;'>❌ هیچ درگاه فعالی یافت نشد</p>";
}

// بررسی تنظیمات IPPanel
echo "<h2>تنظیمات IPPanel:</h2>";
$ippanel_settings = get_option('digit_abzarwp_ippanel', []);
if (!empty($ippanel_settings)) {
    echo "<p style='color: green;'>✅ تنظیمات IPPanel موجود است</p>";
    echo "<pre>" . print_r($ippanel_settings, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel یافت نشد</p>";
}

// بررسی تنظیمات IPPanel (نسخه دیگر)
echo "<h2>تنظیمات IPPanel (نسخه دیگر):</h2>";
$ippanel_settings2 = get_option('digit_ippanel', []);
if (!empty($ippanel_settings2)) {
    echo "<p style='color: green;'>✅ تنظیمات IPPanel نسخه 2 موجود است</p>";
    echo "<pre>" . print_r($ippanel_settings2, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel نسخه 2 یافت نشد</p>";
}

// تست ارسال OTP
echo "<h2>تست ارسال OTP:</h2>";
if (function_exists('digit_send_otp')) {
    echo "<p style='color: green;'>✅ تابع digit_send_otp موجود است</p>";
    
    $test_mobile = '09123456789';
    $test_otp = '1234';
    
    echo "<p>تست ارسال OTP به شماره: $test_mobile</p>";
    
    // تست روش‌های مختلف
    $methods = [
        'method_1' => ['type' => 'sms_otp', 'otp' => $test_otp, 'gateway' => '', 'template' => ''],
        'method_2' => ['type' => 'otp', 'otp' => $test_otp, 'gateway' => '', 'template' => ''],
        'method_3' => ['type' => 'sms_otp', 'otp' => null, 'gateway' => '', 'template' => ''],
        'method_4' => ['type' => 'sms_otp', 'otp' => $test_otp, 'gateway' => 'ippanel', 'template' => '']
    ];
    
    foreach ($methods as $method_name => $params) {
        echo "<h3>$method_name:</h3>";
        $result = digit_send_otp('98', $test_mobile, $params['otp'], $params['type'], $params['gateway'], $params['template']);
        
        if (is_wp_error($result)) {
            echo "<p style='color: red;'>❌ خطا: " . $result->get_error_message() . "</p>";
        } elseif ($result === false) {
            echo "<p style='color: orange;'>⚠️ تابع false برگرداند</p>";
        } else {
            echo "<p style='color: green;'>✅ موفق: " . print_r($result, true) . "</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'>❌ تابع digit_send_otp موجود نیست</p>";
}

// بررسی آپشن‌های مربوط به درگاه‌ها
echo "<h2>تمام آپشن‌های مربوط به درگاه‌ها:</h2>";
global $wpdb;
$gateway_options = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '%digit%' 
    AND (option_name LIKE '%gateway%' OR option_name LIKE '%sms%' OR option_name LIKE '%abzarwp%' OR option_name LIKE '%ippanel%')
    ORDER BY option_name
");

foreach ($gateway_options as $option) {
    echo "<h3>{$option->option_name}:</h3>";
    $value = maybe_unserialize($option->option_value);
    echo "<pre>" . print_r($value, true) . "</pre>";
    echo "<hr>";
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست تنظیمات درگاه Digits</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        pre { background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; overflow-x: auto; }
        h1, h2, h3 { color: #333; }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
    </style>
</head>
<body>
</body>
</html> 