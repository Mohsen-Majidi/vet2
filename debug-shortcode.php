<?php
/**
 * Debug Shortcode Execution
 */

// Include WordPress
require_once('../../../wp-load.php');

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست اجرای Shortcode</title>
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
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .success {
            border-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .info {
            border-color: #17a2b8;
            background: #d1ecf1;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
            white-space: pre-wrap;
            border: 1px solid #ddd;
        }
        .digits-form {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تست اجرای Shortcode Digits</h1>
        
        <?php
        // بررسی وجود shortcode
        $shortcode_exists = shortcode_exists('digits_login');
        
        // بررسی وجود پلاگین Digits
        $digits_active = function_exists('digit_send_otp');
        
        // تست اجرای shortcode
        $shortcode_output = '';
        $shortcode_error = '';
        
        try {
            $shortcode_output = do_shortcode('[digits_login]');
        } catch (Exception $e) {
            $shortcode_error = $e->getMessage();
        }
        
        // بررسی global shortcodes
        global $shortcode_tags;
        ?>
        
        <div class="test-section <?php echo $shortcode_exists ? 'success' : 'error'; ?>">
            <h3>وضعیت Shortcode</h3>
            <p><strong>Shortcode digits_login موجود:</strong> <?php echo $shortcode_exists ? 'بله' : 'خیر'; ?></p>
            <p><strong>پلاگین Digits فعال:</strong> <?php echo $digits_active ? 'بله' : 'خیر'; ?></p>
        </div>
        
        <div class="test-section info">
            <h3>Shortcode های موجود</h3>
            <div class="code">
<?php
if (!empty($shortcode_tags)) {
    foreach ($shortcode_tags as $tag => $callback) {
        echo "Shortcode: [$tag] => " . (is_callable($callback) ? 'Callable' : 'Not Callable') . "\n";
    }
} else {
    echo "هیچ shortcode یافت نشد!";
}
?>
            </div>
        </div>
        
        <div class="test-section <?php echo !empty($shortcode_output) ? 'success' : 'error'; ?>">
            <h3>خروجی Shortcode</h3>
            <?php if (!empty($shortcode_output)): ?>
                <p>Shortcode با موفقیت اجرا شد:</p>
                <div class="digits-form">
                    <?php echo $shortcode_output; ?>
                </div>
            <?php else: ?>
                <p>Shortcode خروجی تولید نکرد!</p>
                <?php if (!empty($shortcode_error)): ?>
                    <p><strong>خطا:</strong> <?php echo $shortcode_error; ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="test-section info">
            <h3>تست های مختلف</h3>
            
            <h4>1. تست با apply_filters</h4>
            <div class="code">
<?php
$test_output = apply_filters('the_content', '[digits_login]');
echo htmlspecialchars($test_output);
?>
            </div>
            
            <h4>2. تست با wp_kses_post</h4>
            <div class="code">
<?php
$test_output2 = wp_kses_post(do_shortcode('[digits_login]'));
echo htmlspecialchars($test_output2);
?>
            </div>
            
            <h4>3. تست با echo</h4>
            <div class="digits-form">
                <?php echo do_shortcode('[digits_login]'); ?>
            </div>
        </div>
        
        <div class="test-section info">
            <h3>راه‌حل های پیشنهادی</h3>
            
            <?php if (!$shortcode_exists): ?>
                <div class="error">
                    <h4>مشکل: Shortcode موجود نیست</h4>
                    <p>راه‌حل:</p>
                    <ol>
                        <li>پلاگین Digits را نصب و فعال کنید</li>
                        <li>تنظیمات Digits را انجام دهید</li>
                        <li>کش را پاک کنید</li>
                    </ol>
                </div>
            <?php elseif (empty($shortcode_output)): ?>
                <div class="error">
                    <h4>مشکل: Shortcode خروجی تولید نمی‌کند</h4>
                    <p>راه‌حل:</p>
                    <ol>
                        <li>تنظیمات Digits را بررسی کنید</li>
                        <li>کش را پاک کنید</li>
                        <li>از کد زیر استفاده کنید:</li>
                    </ol>
                    <div class="code">
// در template:
&lt;?php 
if (function_exists('do_shortcode')) {
    echo do_shortcode('[digits_login]');
} else {
    echo 'Shortcode function not available';
}
?&gt;
                    </div>
                </div>
            <?php else: ?>
                <div class="success">
                    <h4>Shortcode کار می‌کند!</h4>
                    <p>مشکل احتمالاً در template است. از کد زیر استفاده کنید:</p>
                    <div class="code">
&lt;?php echo do_shortcode('[digits_login]'); ?&gt;
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 