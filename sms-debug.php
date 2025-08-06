<?php
/**
 * فایل دیباگ برای بررسی مشکل SMS
 */

require_once('wp-load.php');

echo "<h1>دیباگ مشکل SMS</h1>";

// بررسی تنظیمات IPPanel
echo "<h2>تنظیمات IPPanel:</h2>";
$ippanel_settings = get_option('digit_abzarwp_ippanel', []);
if (!empty($ippanel_settings)) {
    echo "<p style='color: green;'>✅ تنظیمات IPPanel موجود است</p>";
    echo "<pre>" . print_r($ippanel_settings, true) . "</pre>";
    
    // بررسی فیلدهای مهم
    $username = $ippanel_settings['username'] ?? '';
    $password = $ippanel_settings['password'] ?? '';
    $from = $ippanel_settings['from'] ?? '';
    $pid = $ippanel_settings['pid'] ?? '';
    
    echo "<h3>بررسی فیلدهای مهم:</h3>";
    echo "<p>Username: " . (!empty($username) ? "✅ موجود" : "❌ خالی") . "</p>";
    echo "<p>Password: " . (!empty($password) ? "✅ موجود" : "❌ خالی") . "</p>";
    echo "<p>From: " . (!empty($from) ? "✅ موجود ($from)" : "❌ خالی") . "</p>";
    echo "<p>Pattern ID: " . (!empty($pid) ? "✅ موجود ($pid)" : "❌ خالی") . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel یافت نشد</p>";
}

// بررسی تنظیمات IPPanel نسخه دیگر
echo "<h2>تنظیمات IPPanel (نسخه دیگر):</h2>";
$ippanel_settings2 = get_option('digit_ippanel', []);
if (!empty($ippanel_settings2)) {
    echo "<p style='color: green;'>✅ تنظیمات IPPanel نسخه 2 موجود است</p>";
    echo "<pre>" . print_r($ippanel_settings2, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel نسخه 2 یافت نشد</p>";
}

// بررسی تنظیمات عمومی Digits
echo "<h2>تنظیمات عمومی Digits:</h2>";
$general_settings = get_option('digit_general_settings', []);
if (empty($general_settings)) {
    $general_settings = get_option('digits_general_settings', []);
}
echo "<pre>" . print_r($general_settings, true) . "</pre>";

// بررسی درگاه فعال
$active_gateway = $general_settings['gateway'] ?? null;
echo "<h3>درگاه فعال: " . ($active_gateway ?: 'هیچ درگاهی فعال نیست') . "</h3>";

// تست ارسال SMS
echo "<h2>تست ارسال SMS:</h2>";
if (function_exists('digit_send_otp')) {
    echo "<p style='color: green;'>✅ تابع digit_send_otp موجود است</p>";
    
    $test_mobile = '09123456789';
    $test_message = 'تست درگاه SMS - ' . date('Y-m-d H:i:s');
    
    echo "<p>تست ارسال پیام: $test_message</p>";
    echo "<p>به شماره: $test_mobile</p>";
    
    // تست روش‌های مختلف
    $methods = [
        'sms_otp' => ['type' => 'sms_otp', 'message' => $test_message],
        'otp' => ['type' => 'otp', 'message' => $test_message],
        'sms' => ['type' => 'sms', 'message' => $test_message],
        'pattern' => ['type' => 'pattern', 'message' => $test_message]
    ];
    
    foreach ($methods as $method_name => $params) {
        echo "<h3>تست روش $method_name:</h3>";
        $result = digit_send_otp('98', $test_mobile, $params['message'], $params['type'], '', '');
        
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

// بررسی لاگ‌های وردپرس
echo "<h2>بررسی لاگ‌های اخیر:</h2>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $lines = explode("\n", $logs);
    $recent_logs = array_slice($lines, -20); // 20 خط آخر
    
    echo "<pre>";
    foreach ($recent_logs as $line) {
        if (strpos($line, 'DIGITS') !== false || strpos($line, 'SMS') !== false || strpos($line, 'IPPanel') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>فایل لاگ یافت نشد</p>";
}

// بررسی تنظیمات WP_DEBUG
echo "<h2>تنظیمات دیباگ:</h2>";
echo "<p>WP_DEBUG: " . (defined('WP_DEBUG') && WP_DEBUG ? 'فعال' : 'غیرفعال') . "</p>";
echo "<p>WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'فعال' : 'غیرفعال') . "</p>";

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دیباگ مشکل SMS</title>
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