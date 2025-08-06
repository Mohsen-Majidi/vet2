<?php
/**
 * Test file for Digits Integration
 * This file tests the Digits login functionality
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is logged in
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست یکپارچه‌سازی Digits</title>
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
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status.logged-in {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.logged-out {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .digits-form {
            margin-top: 20px;
        }
        .test-info {
            background: #e2e3e5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .test-info h3 {
            margin-top: 0;
        }
        .test-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        /* Digits Form Styles */
        #digits-login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #digits-login-container .digits-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #digits-login-container .digits-form-wrapper {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #digits-login-container .digits-mobile-input input,
        #digits-login-container .digits-form input[type="text"],
        #digits-login-container .digits-form input[type="tel"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s ease;
            text-align: center;
            direction: ltr;
        }

        #digits-login-container .digits-mobile-input input:focus,
        #digits-login-container .digits-form input[type="text"]:focus,
        #digits-login-container .digits-form input[type="tel"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        #digits-login-container .digits-btn {
            width: 100%;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #digits-login-container .digits-btn:hover:not(:disabled) {
            background: #2980b9;
        }

        #digits-login-container .digits-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #digits-login-container .digits-otp-input {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 15px 0;
        }

        #digits-login-container .digits-otp-input input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            transition: border-color 0.3s ease;
            direction: ltr;
        }

        #digits-login-container .digits-otp-input input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        #digits-login-container .digits-resend {
            text-align: center;
            margin-top: 10px;
        }

        #digits-login-container .digits-resend button {
            background: none;
            border: none;
            color: #3498db;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
        }

        #digits-login-container .digits-resend button:hover {
            color: #2980b9;
        }

        #digits-login-container .digits-resend button:disabled {
            color: #999;
            cursor: not-allowed;
            text-decoration: none;
        }

        #digits-login-container .digits-countdown {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        #digits-login-message {
            margin-top: 15px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
            display: none;
        }

        #digits-login-message.show {
            display: block;
        }

        #digits-login-message.error {
            color: #e74c3c;
            background-color: #fdf2f2;
            border: 1px solid #fecaca;
        }

        #digits-login-message.success {
            color: #27ae60;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        #digits-login-message.info {
            color: #3498db;
            background-color: #ebf3fd;
            border: 1px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تست یکپارچه‌سازی Digits</h1>
        
        <div class="status <?php echo $is_logged_in ? 'logged-in' : 'logged-out'; ?>">
            <?php if ($is_logged_in): ?>
                <strong>وضعیت:</strong> وارد شده
                <br>
                <strong>کاربر:</strong> <?php echo esc_html($current_user->display_name); ?>
                <br>
                <strong>ایمیل:</strong> <?php echo esc_html($current_user->user_email); ?>
                <br>
                <strong>شماره موبایل:</strong> <?php echo esc_html(get_user_meta($current_user->ID, 'digits_phone', true)); ?>
            <?php else: ?>
                <strong>وضعیت:</strong> خارج شده
            <?php endif; ?>
        </div>

        <?php if (!$is_logged_in): ?>
            <div class="digits-form">
                <h2>فرم ورود سفارشی</h2>
                <div id="digits-login-container">
                    <div class="digits-form">
                        <div class="digits-form-wrapper">
                            <div class="digits-mobile-input">
                                <input type="tel" name="digits_mobile" id="digits_mobile" placeholder="شماره موبایل" maxlength="11" />
                            </div>
                            <div class="digits-otp-input" style="display: none;">
                                <input type="text" name="digits_otp_1" maxlength="1" />
                                <input type="text" name="digits_otp_2" maxlength="1" />
                                <input type="text" name="digits_otp_3" maxlength="1" />
                                <input type="text" name="digits_otp_4" maxlength="1" />
                                <input type="text" name="digits_otp_5" maxlength="1" />
                                <input type="text" name="digits_otp_6" maxlength="1" />
                            </div>
                            <div class="digits-buttons">
                                <button type="button" id="digits_send_otp" class="digits-btn">ارسال کد تایید</button>
                                <button type="button" id="digits_verify_otp" class="digits-btn" style="display: none;">تایید کد</button>
                            </div>
                            <div class="digits-resend" style="display: none;">
                                <button type="button" id="digits_resend_otp">ارسال مجدد کد</button>
                                <span id="digits_countdown"></span>
                            </div>
                        </div>
                    </div>
                    <div id="digits-login-message"></div>
                </div>
            </div>
        <?php else: ?>
            <div>
                <h2>خروج از حساب</h2>
                <a href="<?php echo wp_logout_url(); ?>" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">خروج</a>
            </div>
        <?php endif; ?>

        <div class="test-info">
            <h3>اطلاعات تست:</h3>
            <ul>
                <li><strong>Digits فعال:</strong> <?php echo function_exists('digit_send_otp') ? 'بله' : 'خیر'; ?></li>
                <li><strong>تابع digit_send_otp:</strong> <?php echo function_exists('digit_send_otp') ? 'موجود' : 'موجود نیست'; ?></li>
                <li><strong>تابع digit_verify_otp:</strong> <?php echo function_exists('digit_verify_otp') ? 'موجود' : 'موجود نیست'; ?></li>
                <li><strong>تنظیمات عمومی Digits:</strong> <?php echo get_option('digits_general_settings') ? 'موجود' : 'موجود نیست'; ?></li>
                <li><strong>تنظیمات OTP Digits:</strong> <?php echo get_option('digits_otp_settings') ? 'موجود' : 'موجود نیست'; ?></li>
            </ul>
        </div>

        <div class="test-info">
            <h3>نحوه تست:</h3>
            <ol>
                <li>اگر وارد نشده‌اید، شماره موبایل خود را وارد کنید</li>
                <li>کد تایید ارسال شده را وارد کنید</li>
                <li>بعد از ورود موفق، وضعیت باید به "وارد شده" تغییر کند</li>
                <li>اطلاعات کاربر باید نمایش داده شود</li>
            </ol>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Test JavaScript integration
        console.log('Digits Integration Test Page Loaded');
        
        // Initialize when document is ready
        $(document).ready(function() {
            console.log('Document ready');
            
            // Handle mobile input
            $('#digits_mobile').on('input', function() {
                let value = $(this).val();
                // Remove non-numeric characters
                value = value.replace(/\D/g, '');
                // Limit to 11 digits
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                $(this).val(value);
            });

            // Handle send OTP button
            $('#digits_send_otp').on('click', function(e) {
                e.preventDefault();
                handleSendOTP();
            });

            // Handle verify OTP button
            $('#digits_verify_otp').on('click', function(e) {
                e.preventDefault();
                handleVerifyOTP();
            });

            // Handle resend OTP button
            $('#digits_resend_otp').on('click', function(e) {
                e.preventDefault();
                handleSendOTP();
            });

            // Handle OTP input fields
            $('.digits-otp-input input').on('input', function() {
                const value = $(this).val();
                if (value.length === 1) {
                    // Move to next input
                    const nextInput = $(this).next('input');
                    if (nextInput.length) {
                        nextInput.focus();
                    } else {
                        // Last input, auto-submit
                        setTimeout(function() {
                            handleVerifyOTP();
                        }, 500);
                    }
                }
            });

            // Handle backspace in OTP inputs
            $('.digits-otp-input input').on('keydown', function(e) {
                if (e.key === 'Backspace' && $(this).val() === '') {
                    const prevInput = $(this).prev('input');
                    if (prevInput.length) {
                        prevInput.focus();
                    }
                }
            });
        });

        // Handle send OTP
        function handleSendOTP() {
            console.log('Handling send OTP...');
            
            const mobile = $('#digits_mobile').val().trim();
            
            if (!mobile) {
                showMessage('لطفاً شماره موبایل را وارد کنید.', 'error');
                return;
            }

            // Validate mobile number
            if (!isValidMobile(mobile)) {
                showMessage('شماره موبایل معتبر نیست.', 'error');
                return;
            }

            showMessage('در حال ارسال کد تایید...', 'info');
            
            // Disable send button
            $('#digits_send_otp').prop('disabled', true);

            // Submit via AJAX
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                method: 'POST',
                data: {
                    action: 'vos_digits_login',
                    mobile: mobile
                },
                success: function(response) {
                    console.log('Send OTP response:', response);
                    
                    if (response.success) {
                        showMessage('کد تایید ارسال شد.', 'success');
                        
                        // Show OTP input
                        $('.digits-mobile-input').hide();
                        $('.digits-otp-input').show();
                        $('.digits-buttons').hide();
                        $('#digits_verify_otp').show();
                        $('.digits-resend').show();
                        
                        // Start countdown
                        startResendCountdown();
                        
                        // Focus first OTP input
                        $('.digits-otp-input input:first').focus();
                    } else {
                        showMessage(response.data?.message || 'خطا در ارسال کد تایید.', 'error');
                        $('#digits_send_otp').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Send OTP error:', error);
                    showMessage('ارتباط با سرور برقرار نشد.', 'error');
                    $('#digits_send_otp').prop('disabled', false);
                }
            });
        }

        // Handle verify OTP
        function handleVerifyOTP() {
            console.log('Handling verify OTP...');
            
            const otp = getOTPValue();
            
            if (!otp || otp.length !== 6) {
                showMessage('لطفاً کد تایید 6 رقمی را وارد کنید.', 'error');
                return;
            }

            showMessage('در حال تایید کد...', 'info');
            
            // Disable verify button
            $('#digits_verify_otp').prop('disabled', true);

            // Submit via AJAX
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                method: 'POST',
                data: {
                    action: 'vos_digits_login',
                    mobile: $('#digits_mobile').val().trim(),
                    otp: otp
                },
                success: function(response) {
                    console.log('Verify OTP response:', response);
                    
                    if (response.success) {
                        showMessage('ورود موفقیت‌آمیز بود!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showMessage(response.data?.message || 'کد تایید اشتباه است.', 'error');
                        $('#digits_verify_otp').prop('disabled', false);
                        
                        // Clear OTP inputs
                        $('.digits-otp-input input').val('');
                        $('.digits-otp-input input:first').focus();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Verify OTP error:', error);
                    showMessage('ارتباط با سرور برقرار نشد.', 'error');
                    $('#digits_verify_otp').prop('disabled', false);
                }
            });
        }

        // Get OTP value from inputs
        function getOTPValue() {
            let otp = '';
            $('.digits-otp-input input').each(function() {
                otp += $(this).val();
            });
            return otp;
        }

        // Start resend countdown
        function startResendCountdown() {
            let countdown = 60;
            const countdownEl = $('#digits_countdown');
            const resendBtn = $('#digits_resend_otp');
            
            resendBtn.prop('disabled', true);
            
            const timer = setInterval(function() {
                countdownEl.text(`ارسال مجدد در ${countdown} ثانیه`);
                countdown--;
                
                if (countdown < 0) {
                    clearInterval(timer);
                    countdownEl.text('');
                    resendBtn.prop('disabled', false);
                }
            }, 1000);
        }

        // Validate mobile number
        function isValidMobile(mobile) {
            const cleanMobile = mobile.replace(/\D/g, '');
            return cleanMobile.length === 11 && cleanMobile.startsWith('09');
        }

        // Show message in the message container
        function showMessage(message, type) {
            const messageEl = document.getElementById('digits-login-message');
            if (messageEl) {
                messageEl.textContent = message;
                messageEl.className = `show ${type}`;
                
                // Auto-hide success messages after 3 seconds
                if (type === 'success') {
                    setTimeout(function() {
                        messageEl.classList.remove('show');
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html> 