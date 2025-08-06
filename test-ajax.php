<?php
/**
 * Test AJAX functionality
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is logged in
$is_logged_in = is_user_logged_in();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست AJAX</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .test-section h3 {
            margin-top: 0;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #2980b9;
        }
        .result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تست AJAX و نانس</h1>
        
        <div class="test-section">
            <h3>1. تست نانس</h3>
            <button onclick="testNonce()">تست نانس</button>
            <div id="nonce-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>2. تست AJAX ساده</h3>
            <button onclick="testSimpleAjax()">تست AJAX ساده</button>
            <div id="simple-ajax-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>3. تست Digits Login</h3>
            <input type="tel" id="test-mobile" placeholder="شماره موبایل" maxlength="11" style="padding: 8px; margin: 5px;">
            <button onclick="testDigitsLogin()">تست Digits Login</button>
            <div id="digits-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>4. اطلاعات سیستم</h3>
            <div class="info">
                <strong>WordPress URL:</strong> <?php echo get_site_url(); ?><br>
                <strong>Admin AJAX URL:</strong> <?php echo admin_url('admin-ajax.php'); ?><br>
                <strong>User Logged In:</strong> <?php echo $is_logged_in ? 'بله' : 'خیر'; ?><br>
                <strong>Digits Plugin:</strong> <?php echo function_exists('digit_send_otp') ? 'فعال' : 'غیرفعال'; ?><br>
                <strong>Nonce Created:</strong> <?php echo wp_create_nonce('vos_phone_nonce'); ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Test nonce
        function testNonce() {
            const result = document.getElementById('nonce-result');
            result.textContent = 'در حال تست نانس...';
            result.className = 'result info';
            
            // Create a simple nonce test
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=vos_digits_login&mobile=09123456789&_ajax_nonce=<?php echo wp_create_nonce('vos_phone_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                result.textContent = 'پاسخ سرور:\n' + JSON.stringify(data, null, 2);
                result.className = 'result success';
            })
            .catch(error => {
                result.textContent = 'خطا:\n' + error.message;
                result.className = 'result error';
            });
        }
        
        // Test simple AJAX
        function testSimpleAjax() {
            const result = document.getElementById('simple-ajax-result');
            result.textContent = 'در حال تست AJAX...';
            result.className = 'result info';
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'vos_digits_login',
                    mobile: '09123456789',
                    _ajax_nonce: '<?php echo wp_create_nonce('vos_phone_nonce'); ?>'
                },
                success: function(response) {
                    result.textContent = 'پاسخ AJAX:\n' + JSON.stringify(response, null, 2);
                    result.className = 'result success';
                },
                error: function(xhr, status, error) {
                    result.textContent = 'خطای AJAX:\nStatus: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText;
                    result.className = 'result error';
                }
            });
        }
        
        // Test Digits Login
        function testDigitsLogin() {
            const mobile = document.getElementById('test-mobile').value.trim();
            const result = document.getElementById('digits-result');
            
            if (!mobile) {
                result.textContent = 'لطفاً شماره موبایل وارد کنید';
                result.className = 'result error';
                return;
            }
            
            result.textContent = 'در حال ارسال درخواست...';
            result.className = 'result info';
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'vos_digits_login',
                    mobile: mobile,
                    _ajax_nonce: '<?php echo wp_create_nonce('vos_phone_nonce'); ?>'
                },
                success: function(response) {
                    result.textContent = 'پاسخ Digits Login:\n' + JSON.stringify(response, null, 2);
                    result.className = 'result success';
                },
                error: function(xhr, status, error) {
                    result.textContent = 'خطای Digits Login:\nStatus: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText;
                    result.className = 'result error';
                }
            });
        }
        
        // Log VOS object if available
        console.log('VOS object:', window.VOS);
        console.log('Nonce available:', window.VOS?.nonces?.phone);
    </script>
</body>
</html> 