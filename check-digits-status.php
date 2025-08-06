<?php
/**
 * Check Digits Plugin Status
 */

// Include WordPress
require_once('../../../wp-load.php');

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بررسی وضعیت پلاگین Digits</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status-item {
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .status-item.success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        .status-item.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .status-item.warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        .status-item.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
            white-space: pre-wrap;
        }
        .digits-form {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>بررسی وضعیت پلاگین Digits</h1>
        
        <?php
        // بررسی وجود پلاگین Digits
        $digits_active = false;
        $digits_functions = [];
        
        if (function_exists('digit_send_otp')) {
            $digits_active = true;
            $digits_functions[] = 'digit_send_otp';
        }
        
        if (function_exists('digit_verify_otp')) {
            $digits_functions[] = 'digit_verify_otp';
        }
        
        if (function_exists('digit_get_user_by_phone')) {
            $digits_functions[] = 'digit_get_user_by_phone';
        }
        
        if (function_exists('digit_create_user')) {
            $digits_functions[] = 'digit_create_user';
        }
        
        if (function_exists('digit_login_user')) {
            $digits_functions[] = 'digit_login_user';
        }
        
        // بررسی تنظیمات Digits
        $digits_settings = [];
        $settings_keys = [
            'digits_general_settings',
            'digits_gateway_settings', 
            'digits_otp_settings',
            'digits_sms_settings',
            'digits_api_settings'
        ];
        
        foreach ($settings_keys as $key) {
            $value = get_option($key);
            if ($value) {
                $digits_settings[$key] = $value;
            }
        }
        
        // بررسی shortcode
        $shortcode_test = do_shortcode('[digits_login]');
        ?>
        
        <div class="status-item <?php echo $digits_active ? 'success' : 'error'; ?>">
            <h3>وضعیت پلاگین Digits</h3>
            <p><strong>فعال:</strong> <?php echo $digits_active ? 'بله' : 'خیر'; ?></p>
        </div>
        
        <div class="status-item <?php echo !empty($digits_functions) ? 'success' : 'error'; ?>">
            <h3>توابع موجود</h3>
            <?php if (!empty($digits_functions)): ?>
                <ul>
                    <?php foreach ($digits_functions as $func): ?>
                        <li><?php echo $func; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>هیچ تابع Digits یافت نشد!</p>
            <?php endif; ?>
        </div>
        
        <div class="status-item <?php echo !empty($digits_settings) ? 'success' : 'warning'; ?>">
            <h3>تنظیمات Digits</h3>
            <?php if (!empty($digits_settings)): ?>
                <ul>
                    <?php foreach ($digits_settings as $key => $value): ?>
                        <li><strong><?php echo $key; ?>:</strong> تنظیم شده</li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>هیچ تنظیمات Digits یافت نشد!</p>
            <?php endif; ?>
        </div>
        
        <div class="status-item <?php echo !empty($shortcode_test) ? 'success' : 'error'; ?>">
            <h3>تست Shortcode</h3>
            <?php if (!empty($shortcode_test)): ?>
                <p>Shortcode [digits_login] کار می‌کند:</p>
                <div class="digits-form">
                    <?php echo $shortcode_test; ?>
                </div>
            <?php else: ?>
                <p>Shortcode [digits_login] کار نمی‌کند!</p>
            <?php endif; ?>
        </div>
        
        <div class="status-item info">
            <h3>پیشنهاد راه‌حل</h3>
            <?php if ($digits_active): ?>
                <p>پلاگین Digits فعال است. پیشنهاد می‌کنم:</p>
                <ol>
                    <li>مستقیماً از shortcode [digits_login] استفاده کنید</li>
                    <li>از API های داخلی Digits استفاده کنید</li>
                    <li>تنظیمات SMS را بررسی کنید</li>
                </ol>
            <?php else: ?>
                <p>پلاگین Digits فعال نیست. لطفاً:</p>
                <ol>
                    <li>پلاگین Digits را نصب و فعال کنید</li>
                    <li>تنظیمات SMS را انجام دهید</li>
                    <li>درگاه SMS را فعال کنید</li>
                </ol>
            <?php endif; ?>
        </div>
        
        <div class="status-item info">
            <h3>کد پیشنهادی</h3>
            <div class="code">
// در template:
&lt;?php echo do_shortcode('[digits_login]'); ?&gt;

// یا برای AJAX:
add_action('wp_ajax_nopriv_digits_login', 'handle_digits_login');
add_action('wp_ajax_digits_login', 'handle_digits_login');

function handle_digits_login() {
    // استفاده از API های داخلی Digits
    if (function_exists('digit_send_otp')) {
        $result = digit_send_otp($country_code, $mobile, $otp, $type);
        // ...
    }
}
            </div>
        </div>
    </div>
</body>
</html> 