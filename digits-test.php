<?php
/**
 * فایل تست برای بررسی وضعیت تنظیمات Digits
 * این فایل را در ریشه سایت قرار دهید و از طریق مرورگر اجرا کنید
 */

// بارگذاری WordPress
require_once('wp-load.php');

// بررسی وضعیت Digits
function test_digits_status() {
    echo "<h2>بررسی وضعیت تنظیمات Digits</h2>";
    
    // بررسی وجود تابع
    if (function_exists('digit_send_otp')) {
        echo "<p style='color: green;'>✅ تابع digit_send_otp موجود است</p>";
    } else {
        echo "<p style='color: red;'>❌ تابع digit_send_otp موجود نیست</p>";
        return;
    }
    
    // بررسی تنظیمات عمومی
    $general_settings = get_option('digit_general_settings', []);
    echo "<h3>تنظیمات عمومی:</h3>";
    echo "<pre>" . print_r($general_settings, true) . "</pre>";
    
    // بررسی تنظیمات درگاه‌ها
    $gateway_settings = get_option('digit_gateway_settings', []);
    echo "<h3>تنظیمات درگاه‌ها:</h3>";
    echo "<pre>" . print_r($gateway_settings, true) . "</pre>";
    
    // بررسی حالت Sandbox
    if (isset($general_settings['sandbox']) && $general_settings['sandbox']) {
        echo "<p style='color: orange;'>⚠️ حالت Sandbox فعال است</p>";
    } else {
        echo "<p style='color: green;'>✅ حالت Sandbox غیرفعال است</p>";
    }
    
    // بررسی درگاه‌های فعال
    $active_gateways = [];
    if (isset($gateway_settings['sms']) && !empty($gateway_settings['sms'])) {
        $active_gateways[] = 'SMS';
    }
    if (isset($gateway_settings['whatsapp']) && !empty($gateway_settings['whatsapp'])) {
        $active_gateways[] = 'WhatsApp';
    }
    
    if (!empty($active_gateways)) {
        echo "<p style='color: green;'>✅ درگاه‌های فعال: " . implode(', ', $active_gateways) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ هیچ درگاه فعالی یافت نشد</p>";
    }
    
    // تست ارسال OTP
    echo "<h3>تست ارسال OTP:</h3>";
    $test_mobile = '09123456789'; // شماره تست
    $test_otp = '1234';
    
    echo "<p>تلاش برای ارسال OTP به شماره: $test_mobile</p>";
    
    $result = digit_send_otp('98', $test_mobile, $test_otp, 'sms_otp', '', '');
    
    if (is_wp_error($result)) {
        echo "<p style='color: red;'>❌ خطا در ارسال: " . $result->get_error_message() . "</p>";
    } elseif ($result === false) {
        echo "<p style='color: red;'>❌ تابع false برگرداند (مشکل تنظیمات)</p>";
    } else {
        echo "<p style='color: green;'>✅ ارسال موفق: " . print_r($result, true) . "</p>";
    }
}

// اجرای تست
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست تنظیمات Digits</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h2, h3 { color: #333; }
    </style>
</head>
<body>
    <?php test_digits_status(); ?>
</body>
</html> 