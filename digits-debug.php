<?php
/**
 * فایل دیباگ دقیق برای بررسی تنظیمات Digits
 */

require_once('wp-load.php');

echo "<h1>دیباگ کامل تنظیمات Digits</h1>";

// بررسی تمام آپشن‌های مربوط به Digits
$digits_options = [
    'digit_general_settings',
    'digit_gateway_settings', 
    'digit_otp_settings',
    'digit_sms_settings',
    'digit_whatsapp_settings',
    'digit_email_settings',
    'digit_advanced_settings'
];

echo "<h2>تمام آپشن‌های Digits:</h2>";
foreach ($digits_options as $option) {
    $value = get_option($option, 'NOT_SET');
    echo "<h3>$option:</h3>";
    echo "<pre>" . print_r($value, true) . "</pre>";
    echo "<hr>";
}

// بررسی آپشن‌های عمومی وردپرس که ممکن است مربوط به Digits باشند
echo "<h2>جستجو در آپشن‌های عمومی:</h2>";
global $wpdb;
$digits_related = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '%digit%' 
    OR option_name LIKE '%sms%' 
    OR option_name LIKE '%gateway%'
    ORDER BY option_name
");

foreach ($digits_related as $option) {
    echo "<h3>{$option->option_name}:</h3>";
    $value = maybe_unserialize($option->option_value);
    echo "<pre>" . print_r($value, true) . "</pre>";
    echo "<hr>";
}

// بررسی پلاگین‌های فعال
echo "<h2>پلاگین‌های فعال:</h2>";
$active_plugins = get_option('active_plugins');
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'digits') !== false) {
        echo "<p style='color: green;'>✅ $plugin</p>";
    }
}

// بررسی نسخه Digits
if (defined('DIGITS_VERSION')) {
    echo "<p>نسخه Digits: " . DIGITS_VERSION . "</p>";
}

// تست تابع digit_send_otp
echo "<h2>تست تابع digit_send_otp:</h2>";
if (function_exists('digit_send_otp')) {
    echo "<p style='color: green;'>✅ تابع موجود است</p>";
    
    // بررسی پارامترهای تابع
    $reflection = new ReflectionFunction('digit_send_otp');
    $params = $reflection->getParameters();
    echo "<p>تعداد پارامترها: " . count($params) . "</p>";
    echo "<p>پارامترها:</p><ul>";
    foreach ($params as $param) {
        echo "<li>{$param->getName()}</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ تابع موجود نیست</p>";
}

// بررسی کلاس‌های Digits
echo "<h2>کلاس‌های Digits:</h2>";
$digits_classes = [
    'Digits',
    'Digits_Sender',
    'Digits_Gateway',
    'Digits_SMS_Gateway'
];

foreach ($digits_classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✅ کلاس $class موجود است</p>";
    } else {
        echo "<p style='color: red;'>❌ کلاس $class موجود نیست</p>";
    }
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دیباگ Digits</title>
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