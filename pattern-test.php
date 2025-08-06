<?php
/**
 * فایل تست برای بررسی مشکل Pattern Code
 */

require_once('wp-load.php');

echo "<h1>تست Pattern Code IPPanel</h1>";

// بررسی تنظیمات IPPanel
$ippanel_settings = get_option('digit_abzarwp_ippanel', []);
$ippanel_settings2 = get_option('digit_ippanel', []);

echo "<h2>تنظیمات IPPanel (AbzarWP):</h2>";
if (!empty($ippanel_settings)) {
    echo "<pre>" . print_r($ippanel_settings, true) . "</pre>";
    
    $username = $ippanel_settings['username'] ?? '';
    $password = $ippanel_settings['password'] ?? '';
    $from = $ippanel_settings['from'] ?? '';
    $pid = $ippanel_settings['pid'] ?? '';
    
    echo "<h3>فیلدهای مهم:</h3>";
    echo "<p>Username: " . (!empty($username) ? "✅ $username" : "❌ خالی") . "</p>";
    echo "<p>Password: " . (!empty($password) ? "✅ موجود" : "❌ خالی") . "</p>";
    echo "<p>From: " . (!empty($from) ? "✅ $from" : "❌ خالی") . "</p>";
    echo "<p>Pattern ID: " . (!empty($pid) ? "✅ $pid" : "❌ خالی") . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel (AbzarWP) یافت نشد</p>";
}

echo "<h2>تنظیمات IPPanel (نسخه دیگر):</h2>";
if (!empty($ippanel_settings2)) {
    echo "<pre>" . print_r($ippanel_settings2, true) . "</pre>";
    
    $username2 = $ippanel_settings2['username'] ?? '';
    $password2 = $ippanel_settings2['password'] ?? '';
    $sender2 = $ippanel_settings2['sender'] ?? '';
    $patterncode2 = $ippanel_settings2['patterncode'] ?? '';
    
    echo "<h3>فیلدهای مهم:</h3>";
    echo "<p>Username: " . (!empty($username2) ? "✅ $username2" : "❌ خالی") . "</p>";
    echo "<p>Password: " . (!empty($password2) ? "✅ موجود" : "❌ خالی") . "</p>";
    echo "<p>Sender: " . (!empty($sender2) ? "✅ $sender2" : "❌ خالی") . "</p>";
    echo "<p>Pattern Code: " . (!empty($patterncode2) ? "✅ $patterncode2" : "❌ خالی") . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ تنظیمات IPPanel (نسخه دیگر) یافت نشد</p>";
}

// تست ارسال با Pattern
echo "<h2>تست ارسال با Pattern:</h2>";
if (function_exists('digit_send_otp')) {
    echo "<p style='color: green;'>✅ تابع digit_send_otp موجود است</p>";
    
    $test_mobile = '09123856521'; // شماره شما
    $test_otp = '1234';
    
    // استفاده از Pattern Code از تنظیمات
    $pattern_code = $ippanel_settings['pid'] ?? $ippanel_settings2['patterncode'] ?? '';
    
    if (!empty($pattern_code)) {
        echo "<p>Pattern Code یافت شد: $pattern_code</p>";
        
        // تست روش‌های مختلف Pattern
        $methods = [
            'pattern_with_otp' => ['type' => 'pattern', 'otp' => $test_otp, 'gateway' => 'ippanel', 'template' => $pattern_code],
            'pattern_without_otp' => ['type' => 'pattern', 'otp' => null, 'gateway' => 'ippanel', 'template' => $pattern_code],
            'pattern_abzarwp' => ['type' => 'pattern', 'otp' => $test_otp, 'gateway' => 'abzarwp_ippanel', 'template' => $pattern_code],
            'sms_otp_with_pattern' => ['type' => 'sms_otp', 'otp' => $test_otp, 'gateway' => 'ippanel', 'template' => $pattern_code]
        ];
        
        foreach ($methods as $method_name => $params) {
            echo "<h3>تست $method_name:</h3>";
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
        echo "<p style='color: red;'>❌ Pattern Code یافت نشد</p>";
        
        // تست بدون Pattern
        echo "<h3>تست بدون Pattern:</h3>";
        $result = digit_send_otp('98', $test_mobile, $test_otp, 'sms_otp', 'ippanel', '');
        
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

// بررسی تنظیمات عمومی
echo "<h2>تنظیمات عمومی Digits:</h2>";
$general_settings = get_option('digit_general_settings', []);
if (empty($general_settings)) {
    $general_settings = get_option('digits_general_settings', []);
}
echo "<pre>" . print_r($general_settings, true) . "</pre>";

$active_gateway = $general_settings['gateway'] ?? null;
echo "<h3>درگاه فعال: " . ($active_gateway ?: 'هیچ درگاهی فعال نیست') . "</h3>";

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست Pattern Code</title>
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