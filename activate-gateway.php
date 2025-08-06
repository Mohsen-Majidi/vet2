<?php
/**
 * فایل تست برای فعال کردن درگاه IPPanel
 */

require_once('wp-load.php');

echo "<h1>فعال کردن درگاه IPPanel</h1>";

// بررسی تنظیمات فعلی
echo "<h2>تنظیمات فعلی:</h2>";
$general_settings = get_option('digit_general_settings', []);
if (empty($general_settings)) {
    $general_settings = get_option('digits_general_settings', []);
}

$current_gateway = $general_settings['gateway'] ?? 'هیچ درگاهی فعال نیست';
echo "<p>درگاه فعال فعلی: <strong>$current_gateway</strong></p>";

// بررسی تنظیمات IPPanel
echo "<h2>تنظیمات IPPanel:</h2>";
$ippanel_settings = get_option('digit_abzarwp_ippanel', []);
if (!empty($ippanel_settings)) {
    echo "<p style='color: green;'>✅ تنظیمات IPPanel موجود است</p>";
    echo "<pre>" . print_r($ippanel_settings, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel یافت نشد</p>";
}

// فعال کردن درگاه
echo "<h2>فعال کردن درگاه IPPanel:</h2>";
if (!empty($ippanel_settings)) {
    // تنظیم درگاه فعال
    $general_settings['gateway'] = 'abzarwp_ippanel';
    
    // ذخیره تنظیمات
    $result = update_option('digit_general_settings', $general_settings);
    
    if ($result) {
        echo "<p style='color: green;'>✅ درگاه IPPanel با موفقیت فعال شد</p>";
        echo "<p>درگاه جدید: <strong>abzarwp_ippanel</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ خطا در ذخیره تنظیمات</p>";
    }
} else {
    echo "<p style='color: red;'>❌ نمی‌توان درگاه را فعال کرد چون تنظیمات IPPanel موجود نیست</p>";
}

// بررسی تنظیمات جدید
echo "<h2>تنظیمات جدید:</h2>";
$new_settings = get_option('digit_general_settings', []);
if (empty($new_settings)) {
    $new_settings = get_option('digits_general_settings', []);
}

$new_gateway = $new_settings['gateway'] ?? 'هیچ درگاهی فعال نیست';
echo "<p>درگاه فعال جدید: <strong>$new_gateway</strong></p>";

// تست ارسال OTP
echo "<h2>تست ارسال OTP:</h2>";
if (function_exists('digit_send_otp')) {
    echo "<p style='color: green;'>✅ تابع digit_send_otp موجود است</p>";
    
    $test_mobile = '09123856521'; // شماره شما
    $test_otp = '1234';
    
    echo "<p>تست ارسال OTP به شماره: $test_mobile</p>";
    
    // تست با درگاه فعال
    $result = digit_send_otp('98', $test_mobile, $test_otp, 'sms_otp', '', '');
    
    if (is_wp_error($result)) {
        echo "<p style='color: red;'>❌ خطا: " . $result->get_error_message() . "</p>";
    } elseif ($result === false) {
        echo "<p style='color: orange;'>⚠️ تابع false برگرداند</p>";
    } else {
        echo "<p style='color: green;'>✅ موفق: " . print_r($result, true) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ تابع digit_send_otp موجود نیست</p>";
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فعال کردن درگاه IPPanel</title>
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