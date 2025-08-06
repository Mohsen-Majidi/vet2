<?php
/**
 * Plugin Name: Vet On Site
 * Description: مدیریت فرم و پنل کاربران و دامپزشک
 * Version: 1.0
 * Author: iNOVA Agency
 */


use WEDEVS\DIGITS\Digits;

defined('ABSPATH') || exit;

define('VOS_PATH', plugin_dir_path(__FILE__));
define('VOS_URL', plugin_dir_url(__FILE__));
define('VOS_DIR', __FILE__);

include_once VOS_PATH . 'includes/form-handler.php';
include_once VOS_PATH . 'includes/user-panel.php';
include_once VOS_PATH . 'includes/vet-panel.php';

add_action('wp_enqueue_scripts', function () {
    if (is_page('دامپزشک-سیار')) {
        global $wp_styles;
        $wp_styles->queue = [];
    }

    wp_enqueue_style('vos-root', VOS_URL . 'static/css/root.css');
    wp_enqueue_style('vos-style', VOS_URL . 'assets/css/style.css', [], time());
    wp_enqueue_script('vos-main', VOS_URL . 'assets/js/main.js', [], null, true);
    wp_enqueue_script('vos-custom', VOS_URL . 'assets/js/custom.js', [], null, true);
    wp_enqueue_script('vos-inputHandle', VOS_URL . 'assets/js/inputHandle.js', [], null, true);
    wp_enqueue_script('vos-digits-integration', VOS_URL . 'assets/js/digits-integration.js', ['jquery'], null, true);
    wp_enqueue_script('jquery');

    // متغیرهای Ajax
    wp_localize_script('vos-custom', 'VOS', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'url' => VOS_URL,
        'nonces' => [
            'phone' => wp_create_nonce('vos_phone_nonce'),
        ],
    ]);

    // متغیرهای Ajax برای digits-integration
    wp_localize_script('vos-digits-integration', 'VOS', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'url' => VOS_URL,
        'nonces' => [
            'phone' => wp_create_nonce('vos_phone_nonce'),
        ],
    ]);
});

//add user_addresses table
register_activation_hook(__FILE__, 'vos_install_user_addresses_table');

function vos_install_user_addresses_table()
{
    global $wpdb;

    $table = $wpdb->prefix . 'user_addresses';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        address_name VARCHAR(191) NOT NULL,
        address_city VARCHAR(100) NOT NULL,
        address_province VARCHAR(100) NOT NULL,
        address_dl TEXT NOT NULL,
        latitude  DECIMAL(10,6),
        longitude  DECIMAL(10,6),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

//remove wp styles
add_filter('template_include', function ($template) {
    if (is_page('دامپزشک-سیار')) {
        return VOS_PATH . 'templates/vetonsite-page.php';
    }
    return $template;
});

// تبدیل اعداد فارسی به انگلیسی
function vos_fa_to_en($str)
{
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($fa, $en, $str);
}

//digits check
add_action('wp_ajax_nopriv_check_mobile_digits', 'check_mobile_digits');
add_action('wp_ajax_check_mobile_digits', 'check_mobile_digits');
function check_mobile_digits()
{

    header('Content-Type: application/json; charset=utf-8');
    $raw_body = file_get_contents('php://input');
    $json = json_decode($raw_body, true);
    if (is_array($json)) {
        // اولویت به مقادیر موجود در JSON
        $_POST = array_merge($_POST, $json);
        $_REQUEST = array_merge($_REQUEST, $json);
    }

    // بررسی نانس یا توکن
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';

    $is_valid = false;

    // بررسی نانس
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $is_valid = true;
    }

    // بررسی توکن (برای تست‌های خارجی)
    if ($token && !$is_valid) {
        $token_data = get_transient('vos_test_token_' . $token);
        if ($token_data) {
            $is_valid = true;
        }
    }

    if (!$is_valid) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    $raw = (string)($_POST['mobile'] ?? '');
    $phone = vos_fa_to_en($raw);
    $phone = preg_replace('/\D+/', '', $phone);

    if ($phone === '') {
        wp_send_json_success([
            'ok' => false,
            'code' => 'required',
            'message' => 'شماره موبایل را وارد کنید.',
            'user_id' => null,
            'token' => $token
        ]);
    }

    if (!preg_match('/^\d{11}$/', $phone)) {
        wp_send_json_success([
            'ok' => false,
            'code' => 'invalid_length',
            'message' => 'شماره باید دقیقاً ۱۱ رقم باشد.',
            'user_id' => null,
            'token' => $token
        ]);
    }

    if (strpos($phone, '09') !== 0) {
        wp_send_json_success([
            'ok' => false,
            'code' => 'invalid_prefix',
            'message' => 'شماره باید با 09 شروع شود.',
            'user_id' => null,
            'token' => $token
        ]);
    }

    $local = $phone;
    $e164 = '+98' . substr($phone, 1);

    // جستجو روی متاهای رایج Digits
    $keys = ['_digits_phone_std', '_billing_phone_std_user', 'digits_phone', 'digits_phone_no', '_billing_phone'];
    $mq = ['relation' => 'OR'];
    foreach ($keys as $k) {
        $mq[] = ['key' => $k, 'value' => $local, 'compare' => '='];
        $mq[] = ['key' => $k, 'value' => $e164, 'compare' => '='];
    }

    $users = get_users(['number' => 1, 'fields' => 'ids', 'meta_query' => $mq]);
    $user_id = !empty($users) ? (int)$users[0] : 0;

    error_log('DEBUG_TOKEN = ' . var_export($token, true));

    if ($user_id) {                        // همان شرط موجود شما
        $expires = time() + DAY_IN_SECONDS * 30;
        header(
            'Set-Cookie: vos_token=' . urlencode($token)
            . '; Expires=' . gmdate('D, d M Y H:i:s T', $expires)
            . '; Path=/'
            . '; Domain=' . parse_url(site_url(), PHP_URL_HOST)
            . '; Secure; HttpOnly; SameSite=None'
        );
        set_transient('vos_user_token_' . $token, $user_id, DAY_IN_SECONDS * 30);
    }


    wp_send_json_success([
        'ok' => true,
        'code' => $user_id ? 'registered' : 'not_registered',
        'message' => $user_id ? 'شماره پیدا شد. می‌توانید ادامه دهید.' : 'این شماره ثبت نشده است.',
        'phone' => ['digits' => $local, 'e164' => $e164],
        'user_id' => $user_id ?: null,
        'token' => $token
    ]);
}

// API برای دریافت نانس
add_action('wp_ajax_nopriv_vos_get_nonce', 'vos_get_nonce');
add_action('wp_ajax_vos_get_nonce', 'vos_get_nonce');
function vos_get_nonce()
{
    wp_send_json_success([
        'nonce' => wp_create_nonce('vos_phone_nonce'),
        'timestamp' => time(),
        'expires_in' => 24 * 60 * 60 // 24 ساعت
    ]);
}

// API برای دریافت توکن (برای تست‌های خارجی)
add_action('wp_ajax_nopriv_vos_get_token', 'vos_get_token');
add_action('wp_ajax_vos_get_token', 'vos_get_token');
function vos_get_token()
{
    $token = wp_generate_password(32, false, false);
    set_transient('vos_test_token_' . $token, ['created' => time()], 24 * 60 * 60);

    wp_send_json_success([
        'token' => $token,
        'timestamp' => time(),
        'expires_in' => 24 * 60 * 60
    ]);
}

// AJAX handler برای لود کردن shortcode Digits
//add_action('wp_ajax_nopriv_load_dm_page_shortcode', 'vos_load_dm_page_shortcode_ajax');
//add_action('wp_ajax_load_dm_page_shortcode', 'vos_load_dm_page_shortcode_ajax');
//function vos_load_dm_page_shortcode_ajax()
//{
//    header('Content-Type: application/json; charset=utf-8');
//    if (!check_ajax_referer('vos_dm_nonce', '_ajax_nonce', false)) {
//        wp_send_json_error(['message' => 'Invalid nonce']);
//    }
//    $shortcode_output = '';
//    if (function_exists('do_shortcode') && shortcode_exists('dm-page')) {
//        $shortcode_output = do_shortcode('[dm-page]');
//    }
//    if (!empty($shortcode_output) && $shortcode_output !== '[dm-page]') {
//        wp_send_json_success([
//            'html' => $shortcode_output,
//            'message' => 'Shortcode loaded successfully'
//        ]);
//    } else {
//        wp_send_json_error([
//            'message' => 'Shortcode produced no output',
//            'fallback_html' => '<div class="dm-fallback-form">
//                <p>فرم بارگذاری نشد. لطفاً رفرش کنید یا با پشتیبانی تماس بگیرید.</p>
//            </div>'
//        ]);
//    }
//}


// API برای بررسی وضعیت Digits
add_action('wp_ajax_nopriv_vos_check_digits', 'vos_check_digits_ajax');
add_action('wp_ajax_vos_check_digits', 'vos_check_digits_ajax');
function vos_check_digits_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    $status = vos_check_digits_status();
    wp_send_json_success($status);
}

// API برای تست مستقیم Digits
add_action('wp_ajax_nopriv_vos_test_digits', 'vos_test_digits_ajax');
add_action('wp_ajax_vos_test_digits', 'vos_test_digits_ajax');
function vos_test_digits_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    if (!function_exists('digit_send_otp')) {
        wp_send_json_error(['message' => 'تابع digit_send_otp موجود نیست'], 500);
    }

    // تست با شماره ثابت
    $test_mobile = '09123456789';
    $test_otp = '1234';

    $result = digit_send_otp('98', $test_mobile, $test_otp, 'sms_otp', '', '');

    $response = [
        'test_mobile' => $test_mobile,
        'test_otp' => $test_otp,
        'result_type' => gettype($result),
        'result_value' => $result
    ];

    if (is_wp_error($result)) {
        $response['error'] = [
            'code' => $result->get_error_code(),
            'message' => $result->get_error_message()
        ];
        wp_send_json_error($response, 500);
    } elseif ($result === false) {
        $response['error'] = 'تابع false برگرداند';
        wp_send_json_error($response, 500);
    } else {
        $response['success'] = true;
        wp_send_json_success($response);
    }
}

// API برای بررسی تنظیمات IPPanel
add_action('wp_ajax_nopriv_vos_check_ippanel', 'vos_check_ippanel_ajax');
add_action('wp_ajax_vos_check_ippanel', 'vos_check_ippanel_ajax');
function vos_check_ippanel_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    $ippanel_status = vos_check_ippanel_settings();
    wp_send_json_success($ippanel_status);
}

// API برای بررسی درگاه فعال
add_action('wp_ajax_nopriv_vos_check_active_gateway', 'vos_check_active_gateway_ajax');
add_action('wp_ajax_vos_check_active_gateway', 'vos_check_active_gateway_ajax');
function vos_check_active_gateway_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    $gateway_status = vos_check_active_gateway();
    wp_send_json_success($gateway_status);
}

// API برای تست مستقیم درگاه SMS
add_action('wp_ajax_nopriv_vos_test_sms_gateway', 'vos_test_sms_gateway_ajax');
add_action('wp_ajax_vos_test_sms_gateway', 'vos_test_sms_gateway_ajax');
function vos_test_sms_gateway_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    // دریافت شماره موبایل


    $mobile = preg_replace('/\D+/', '', vos_fa_to_en((string)($_POST['mobile'] ?? '')));
    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'شماره موبایل نامعتبر است'], 422);
    }

    // دریافت پیام (اختیاری)
    $message = sanitize_text_field($_POST['message'] ?? '');

    $test_result = vos_test_sms_gateway($mobile, $message);
    wp_send_json_success($test_result);
}

// API برای دیباگ تنظیمات IPPanel
add_action('wp_ajax_nopriv_vos_debug_ippanel', 'vos_debug_ippanel_ajax');
add_action('wp_ajax_vos_debug_ippanel', 'vos_debug_ippanel_ajax');
function vos_debug_ippanel_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // ادغام JSON با $_POST
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // بررسی نانس یا توکن
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';

    $is_valid = false;

    // بررسی نانس
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $is_valid = true;
    }

    // بررسی توکن (برای تست‌های خارجی)
    if ($token && !$is_valid) {
        $token_data = get_transient('vos_test_token_' . $token);
        if ($token_data) {
            $is_valid = true;
        }
    }

    if (!$is_valid) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    $debug_info = vos_debug_ippanel_settings();
    wp_send_json_success($debug_info);
}

// API برای فعال کردن درگاه IPPanel
add_action('wp_ajax_nopriv_vos_activate_ippanel', 'vos_activate_ippanel_ajax');
add_action('wp_ajax_vos_activate_ippanel', 'vos_activate_ippanel_ajax');
function vos_activate_ippanel_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    $result = vos_activate_ippanel_gateway();
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result, 500);
    }
}

// API برای تست مستقیم IPPanel
add_action('wp_ajax_nopriv_vos_test_ippanel_direct', 'vos_test_ippanel_direct_ajax');
add_action('wp_ajax_vos_test_ippanel_direct', 'vos_test_ippanel_direct_ajax');
function vos_test_ippanel_direct_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // بررسی نانس
    if (!isset($_POST['_ajax_nonce']) || !check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'invalid nonce'], 400);
    }

    // دریافت شماره موبایل
    $mobile = preg_replace('/\D+/', '', vos_fa_to_en((string)($_POST['mobile'] ?? '')));
    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'شماره موبایل نامعتبر است'], 422);
    }

    // دریافت پیام (اختیاری)
    $message = sanitize_text_field($_POST['message'] ?? '');

    $test_result = vos_test_ippanel_directly($mobile, $message);
    wp_send_json_success($test_result);
}

// API برای تست مستقیم API IPPanel
add_action('wp_ajax_nopriv_vos_test_ippanel_api', 'vos_test_ippanel_api_ajax');
add_action('wp_ajax_vos_test_ippanel_api', 'vos_test_ippanel_api_ajax');
function vos_test_ippanel_api_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // ادغام JSON با $_POST
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // بررسی نانس یا توکن
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';

    $is_valid = false;

    // بررسی نانس
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $is_valid = true;
    }

    // بررسی توکن (برای تست‌های خارجی)
    if ($token && !$is_valid) {
        $token_data = get_transient('vos_test_token_' . $token);
        if ($token_data) {
            $is_valid = true;
        }
    }

    if (!$is_valid) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    // دریافت شماره موبایل
    $mobile = preg_replace('/\D+/', '', vos_fa_to_en((string)($_POST['mobile'] ?? '')));
    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'شماره موبایل نامعتبر است'], 422);
    }

    // دریافت OTP (اختیاری)
    $otp = sanitize_text_field($_POST['otp'] ?? '1234');

    $test_result = vos_test_ippanel_api($mobile, $otp);
    wp_send_json_success($test_result);
}

// API برای تست تأیید OTP
add_action('wp_ajax_nopriv_vos_test_verify_otp', 'vos_test_verify_otp_ajax');
add_action('wp_ajax_vos_test_verify_otp', 'vos_test_verify_otp_ajax');

// Fix IPPanel settings
add_action('wp_ajax_vos_fix_ippanel', 'vos_fix_ippanel_ajax');
add_action('wp_ajax_nopriv_vos_fix_ippanel', 'vos_fix_ippanel_ajax');
function vos_test_verify_otp_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // ادغام JSON با $_POST
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // بررسی نانس یا توکن
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';

    $is_valid = false;

    // بررسی نانس
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $is_valid = true;
    }

    // بررسی توکن (برای تست‌های خارجی)
    if ($token && !$is_valid) {
        $token_data = get_transient('vos_test_token_' . $token);
        if ($token_data) {
            $is_valid = true;
        }
    }

    if (!$is_valid) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    // دریافت شماره موبایل
    $mobile = preg_replace('/\D+/', '', vos_fa_to_en((string)($_POST['mobile'] ?? '')));
    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'شماره موبایل نامعتبر است'], 422);
    }

    // دریافت OTP (اختیاری برای vos_send_otp)
    $otp = sanitize_text_field($_POST['otp'] ?? '');
    // برای vos_send_otp، OTP اختیاری است و اگر ارسال نشود، خودش تولید می‌شود

    $test_result = vos_test_verify_otp($mobile, $otp);
    wp_send_json_success($test_result);
}

//todo remove it
add_filter('digits_otp_force_send', '__return_true');

// تابع بررسی وضعیت تنظیمات Digits
function vos_check_digits_status()
{
    $status = [
        'plugin_active' => false,
        'function_exists' => false,
        'gateway_configured' => false,
        'sandbox_mode' => false,
        'details' => []
    ];

    // بررسی فعال بودن پلاگین
    if (function_exists('digit_send_otp')) {
        $status['plugin_active'] = true;
        $status['function_exists'] = true;
    }

    // بررسی تنظیمات درگاه (اگر تابع موجود باشد)
    if ($status['function_exists']) {
        // بررسی تمام آپشن‌های ممکن Digits
        $possible_options = [
            'digit_general_settings',
            'digit_gateway_settings',
            'digit_otp_settings',
            'digit_sms_settings',
            'digit_whatsapp_settings',
            'digit_email_settings',
            'digit_advanced_settings',
            'digits_general_settings',
            'digits_gateway_settings',
            'digits_otp_settings',
            'digits_sms_settings',
            'digits_whatsapp_settings',
            'digits_email_settings',
            'digits_advanced_settings'
        ];

        // جستجو در تمام آپشن‌های مربوط به درگاه‌ها
        global $wpdb;
        $gateway_options = $wpdb->get_results("
            SELECT option_name, option_value
            FROM {$wpdb->options}
            WHERE option_name LIKE '%digit%'
            AND (option_name LIKE '%gateway%' OR option_name LIKE '%sms%' OR option_name LIKE '%abzarwp%' OR option_name LIKE '%ippanel%')
            ORDER BY option_name
        ");

        foreach ($gateway_options as $option) {
            $possible_options[] = $option->option_name;
        }

        $all_settings = [];
        foreach ($possible_options as $option) {
            $value = get_option($option, null);
            if ($value !== null) {
                $all_settings[$option] = $value;
            }
        }
        $status['details']['all_settings'] = $all_settings;

        // بررسی تنظیمات عمومی Digits
        $digits_settings = get_option('digit_general_settings', []);
        if (empty($digits_settings)) {
            $digits_settings = get_option('digits_general_settings', []);
        }
        $status['details']['general_settings'] = $digits_settings;

        // بررسی درگاه‌های فعال
        $gateways = get_option('digit_gateway_settings', []);
        if (empty($gateways)) {
            $gateways = get_option('digits_gateway_settings', []);
        }
        $status['details']['gateways'] = $gateways;

        // بررسی حالت Sandbox
        if (isset($digits_settings['sandbox']) && $digits_settings['sandbox']) {
            $status['sandbox_mode'] = true;
        }

        // بررسی درگاه‌های فعال - روش‌های مختلف
        $active_gateways = [];

        // روش 1: بررسی مستقیم
        if (isset($gateways['sms']) && !empty($gateways['sms'])) {
            $active_gateways[] = 'sms';
        }
        if (isset($gateways['whatsapp']) && !empty($gateways['whatsapp'])) {
            $active_gateways[] = 'whatsapp';
        }

        // روش 1.5: بررسی آپشن‌های خاص درگاه‌ها
        foreach ($all_settings as $option_name => $settings) {
            // بررسی آپشن‌های مربوط به درگاه‌های مختلف
            if (strpos($option_name, 'abzarwp') !== false ||
                strpos($option_name, 'ippanel') !== false ||
                strpos($option_name, 'melipayamak') !== false ||
                strpos($option_name, 'kaveh') !== false ||
                strpos($option_name, 'smsir') !== false ||
                strpos($option_name, 'national') !== false) {

                if (!empty($settings) && is_array($settings)) {
                    // بررسی اینکه آیا اطلاعات درگاه موجود است
                    $has_credentials = false;
                    if (isset($settings['username']) && !empty($settings['username'])) {
                        $has_credentials = true;
                    }
                    if (isset($settings['password']) && !empty($settings['password'])) {
                        $has_credentials = true;
                    }
                    if (isset($settings['api_key']) && !empty($settings['api_key'])) {
                        $has_credentials = true;
                    }
                    if (isset($settings['secret_key']) && !empty($settings['secret_key'])) {
                        $has_credentials = true;
                    }

                    if ($has_credentials) {
                        $gateway_name = str_replace(['digit_', 'digits_'], '', $option_name);
                        $active_gateways[] = $gateway_name;
                        $status['details']['detected_gateway'] = $option_name;
                    }
                }
            }
        }

        // روش 2: بررسی از طریق کلاس Digits
        if (class_exists('Digits')) {
            try {
                $digits_instance = Digits::getInstance();
                if (method_exists($digits_instance, 'getGatewaySettings')) {
                    $class_gateways = $digits_instance->getGatewaySettings();
                    if (!empty($class_gateways)) {
                        $status['details']['class_gateways'] = $class_gateways;
                        if (isset($class_gateways['sms']) && !empty($class_gateways['sms'])) {
                            $active_gateways[] = 'sms';
                        }
                    }
                }
            } catch (Exception $e) {
                $status['details']['class_error'] = $e->getMessage();
            }
        }

        // روش 3: بررسی از طریق تابع مستقیم
        if (function_exists('digits_get_gateway_settings')) {
            $func_gateways = digits_get_gateway_settings();
            if (!empty($func_gateways)) {
                $status['details']['func_gateways'] = $func_gateways;
                if (isset($func_gateways['sms']) && !empty($func_gateways['sms'])) {
                    $active_gateways[] = 'sms';
                }
            }
        }

        // روش 4: بررسی از طریق دیتابیس مستقیم
        global $wpdb;
        $db_gateways = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'digit_gateway_settings'");
        if ($db_gateways) {
            $db_gateways_data = maybe_unserialize($db_gateways);
            $status['details']['db_gateways'] = $db_gateways_data;
            if (is_array($db_gateways_data) && isset($db_gateways_data['sms']) && !empty($db_gateways_data['sms'])) {
                $active_gateways[] = 'sms';
            }
        }

        // حذف موارد تکراری
        $active_gateways = array_unique($active_gateways);

        if (!empty($active_gateways)) {
            $status['gateway_configured'] = true;
            $status['details']['active_gateways'] = $active_gateways;
        }

        // تست مستقیم ارسال OTP
        $status['details']['test_result'] = 'not_tested';
        try {
            $test_result = digit_send_otp('98', '09123456789', '1234', 'sms_otp', '', '');
            if (is_wp_error($test_result)) {
                $status['details']['test_result'] = 'wp_error: ' . $test_result->get_error_message();
            } elseif ($test_result === false) {
                $status['details']['test_result'] = 'returned_false';
            } else {
                $status['details']['test_result'] = 'success: ' . print_r($test_result, true);
            }
        } catch (Exception $e) {
            $status['details']['test_result'] = 'exception: ' . $e->getMessage();
        }
    }

    return $status;
}

// تابع بررسی تنظیمات IPPanel
function vos_check_ippanel_settings()
{
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);

    if (empty($ippanel_settings)) {
        return [
            'configured' => false,
            'message' => 'تنظیمات IPPanel یافت نشد'
        ];
    }

    $has_username = !empty($ippanel_settings['username']);
    $has_password = !empty($ippanel_settings['password']);

    return [
        'configured' => $has_username && $has_password,
        'username_set' => $has_username,
        'password_set' => $has_password,
        'settings' => $ippanel_settings,
        'message' => $has_username && $has_password
            ? 'تنظیمات IPPanel کامل است'
            : 'تنظیمات IPPanel ناقص است'
    ];
}

// تابع بررسی درگاه فعال در تنظیمات Digits
function vos_check_active_gateway()
{
    // بررسی تنظیمات عمومی Digits
    $general_settings = get_option('digit_general_settings', []);
    if (empty($general_settings)) {
        $general_settings = get_option('digits_general_settings', []);
    }

    // بررسی درگاه فعال
    $active_gateway = null;
    if (isset($general_settings['gateway'])) {
        $active_gateway = $general_settings['gateway'];
    }

    // بررسی تنظیمات OTP
    $otp_settings = get_option('digit_otp_settings', []);
    if (empty($otp_settings)) {
        $otp_settings = get_option('digits_otp_settings', []);
    }

    return [
        'general_settings' => $general_settings,
        'otp_settings' => $otp_settings,
        'active_gateway' => $active_gateway,
        'gateway_enabled' => !empty($active_gateway)
    ];
}

// تابع تست مستقیم درگاه SMS
function vos_test_sms_gateway($mobile, $message = null)
{
    if (!$message) {
        $message = 'تست درگاه SMS - ' . date('Y-m-d H:i:s');
    }

    // تست با تابع مستقیم Digits
    if (function_exists('digit_send_otp')) {
        $result = digit_send_otp('98', $mobile, $message, 'sms', '', '');

        return [
            'method' => 'digit_send_otp',
            'result' => $result,
            'is_wp_error' => is_wp_error($result),
            'is_false' => $result === false,
            'error_message' => is_wp_error($result) ? $result->get_error_message() : null
        ];
    }

    // تست با تابع ارسال SMS عمومی
    if (function_exists('digits_send_sms')) {
        $result = digits_send_sms($message, $mobile);

        return [
            'method' => 'digits_send_sms',
            'result' => $result,
            'is_wp_error' => is_wp_error($result),
            'is_false' => $result === false,
            'error_message' => is_wp_error($result) ? $result->get_error_message() : null
        ];
    }

    return [
        'method' => 'none',
        'result' => null,
        'error' => 'هیچ تابع ارسال SMS یافت نشد'
    ];
}

// تابع بررسی دقیق تنظیمات IPPanel
function vos_debug_ippanel_settings()
{
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);
    $ippanel_settings2 = get_option('digit_ippanel', []);

    $debug_info = [
        'abzarwp_ippanel' => [
            'exists' => !empty($ippanel_settings),
            'settings' => $ippanel_settings,
            'username' => $ippanel_settings['username'] ?? '',
            'password' => $ippanel_settings['password'] ?? '',
            'from' => $ippanel_settings['from'] ?? '',
            'pid' => $ippanel_settings['pid'] ?? '',
            'has_credentials' => !empty($ippanel_settings['username']) && !empty($ippanel_settings['password']),
            'has_from' => !empty($ippanel_settings['from']),
            'has_pattern' => !empty($ippanel_settings['pid'])
        ],
        'ippanel' => [
            'exists' => !empty($ippanel_settings2),
            'settings' => $ippanel_settings2,
            'username' => $ippanel_settings2['username'] ?? '',
            'password' => $ippanel_settings2['password'] ?? '',
            'sender' => $ippanel_settings2['sender'] ?? '',
            'patterncode' => $ippanel_settings2['patterncode'] ?? '',
            'has_credentials' => !empty($ippanel_settings2['username']) && !empty($ippanel_settings2['password']),
            'has_sender' => !empty($ippanel_settings2['sender']),
            'has_pattern' => !empty($ippanel_settings2['patterncode'])
        ]
    ];

    // بررسی تنظیمات عمومی
    $general_settings = get_option('digit_general_settings', []);
    if (empty($general_settings)) {
        $general_settings = get_option('digits_general_settings', []);
    }

    $debug_info['general_settings'] = $general_settings;
    $debug_info['active_gateway'] = $general_settings['gateway'] ?? null;

    return $debug_info;
}

// تابع فعال کردن درگاه IPPanel
function vos_activate_ippanel_gateway()
{
    // بررسی تنظیمات IPPanel
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);
    $ippanel_settings2 = get_option('digit_ippanel', []);

    // انتخاب تنظیمات موجود
    $settings_to_use = !empty($ippanel_settings) ? $ippanel_settings : $ippanel_settings2;

    if (empty($settings_to_use)) {
        return [
            'success' => false,
            'message' => 'تنظیمات IPPanel یافت نشد'
        ];
    }

    // فعال کردن درگاه در تنظیمات عمومی
    $general_settings = get_option('digit_general_settings', []);
    if (empty($general_settings)) {
        $general_settings = get_option('digits_general_settings', []);
    }

    // تنظیم درگاه فعال
    $general_settings['gateway'] = 'abzarwp_ippanel';

    // ذخیره تنظیمات
    $result = update_option('digit_general_settings', $general_settings);

    if ($result) {
        return [
            'success' => true,
            'message' => 'درگاه IPPanel فعال شد',
            'gateway' => 'abzarwp_ippanel',
            'settings' => $general_settings
        ];
    } else {
        return [
            'success' => false,
            'message' => 'خطا در ذخیره تنظیمات'
        ];
    }
}

// تابع تست مستقیم IPPanel
function vos_test_ippanel_directly($mobile, $message = null)
{
    if (!$message) {
        $message = 'تست مستقیم IPPanel - ' . date('Y-m-d H:i:s');
    }

    // دریافت تنظیمات IPPanel
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);

    if (empty($ippanel_settings)) {
        return [
            'success' => false,
            'error' => 'تنظیمات IPPanel یافت نشد'
        ];
    }

    $username = $ippanel_settings['username'] ?? '';
    $password = $ippanel_settings['password'] ?? '';
    $from = $ippanel_settings['from'] ?? '';
    $pid = $ippanel_settings['pid'] ?? '';

    // بررسی فیلدهای ضروری
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'error' => 'Username یا Password تنظیم نشده'
        ];
    }

    // تست ارسال مستقیم با IPPanel
    $test_results = [];

    // تست 1: ارسال معمولی
    $result1 = digit_send_otp('98', $mobile, $message, 'sms_otp', 'abzarwp_ippanel', '');
    $test_results['normal_sms'] = [
        'result' => $result1,
        'is_wp_error' => is_wp_error($result1),
        'is_false' => $result1 === false,
        'error_message' => is_wp_error($result1) ? $result1->get_error_message() : null
    ];

    // تست 2: ارسال با Pattern
    if (!empty($pid)) {
        $result2 = digit_send_otp('98', $mobile, $message, 'pattern', 'abzarwp_ippanel', $pid);
        $test_results['pattern_sms'] = [
            'result' => $result2,
            'is_wp_error' => is_wp_error($result2),
            'is_false' => $result2 === false,
            'error_message' => is_wp_error($result2) ? $result2->get_error_message() : null
        ];
    }

    // تست 3: ارسال بدون مشخص کردن درگاه
    $result3 = digit_send_otp('98', $mobile, $message, 'sms_otp', '', '');
    $test_results['auto_gateway'] = [
        'result' => $result3,
        'is_wp_error' => is_wp_error($result3),
        'is_false' => $result3 === false,
        'error_message' => is_wp_error($result3) ? $result3->get_error_message() : null
    ];

    return [
        'success' => true,
        'settings' => [
            'username' => $username,
            'password' => !empty($password) ? 'set' : 'not_set',
            'from' => $from,
            'pid' => $pid
        ],
        'test_results' => $test_results
    ];
}

// تابع تست مستقیم با API IPPanel
function vos_test_ippanel_api($mobile, $otp)
{
    // دریافت تنظیمات IPPanel
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);

    if (empty($ippanel_settings)) {
        return [
            'success' => false,
            'error' => 'تنظیمات IPPanel یافت نشد'
        ];
    }

    $username = $ippanel_settings['username'] ?? '';
    $password = $ippanel_settings['password'] ?? '';
    $from = $ippanel_settings['from'] ?? '';
    $pid = $ippanel_settings['pid'] ?? '';

    // تست ارسال مستقیم با API IPPanel
    $api_url = 'https://ippanel.com/api/select';
    $data = [
        'username' => $username,
        'password' => $password,
        'to' => $mobile,
        'from' => $from,
        'text' => "کد تایید شما: $otp"
    ];

    // ارسال درخواست
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    return [
        'success' => true,
        'api_url' => $api_url,
        'data_sent' => $data,
        'response' => $response,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'settings_used' => [
            'username' => $username,
            'password' => !empty($password) ? 'set' : 'not_set',
            'from' => $from,
            'pid' => $pid
        ]
    ];
}

// Add AJAX handlers for user addresses
add_action('wp_ajax_save_user_address', 'vos_save_user_address');
add_action('wp_ajax_nopriv_save_user_address', 'vos_save_user_address');
add_action('wp_ajax_vos_save_address', 'vos_save_user_address');
add_action('wp_ajax_nopriv_vos_save_address', 'vos_save_user_address');

function vos_save_user_address()
{
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'کاربر لاگین نیست']);
        return;
    }

    $user_id = get_current_user_id();
    $address_name = sanitize_text_field($_POST['address_name']);
    $address_city = sanitize_text_field($_POST['address_city']);
    $address_province = sanitize_text_field($_POST['address_province']);
    $address_dl = sanitize_textarea_field($_POST['address_dl']);

    $lat   = floatval( $_POST['latitude']  ?? 0 );
    $lng   = floatval( $_POST['longitude'] ?? 0 );

    // Validate required fields
    if (empty($address_name) || empty($address_city) || empty($address_province) || empty($address_dl)) {
        wp_send_json(['success' => false, 'message' => 'تمام فیلدها الزامی هستند']);
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_addresses';

    $result = $wpdb->insert(
        $table,
        [
            'user_id' => $user_id,
            'address_name' => $address_name,
            'address_city' => $address_city,
            'address_province' => $address_province,
            'address_dl' => $address_dl,
            'latitude'        => $lat,
            'longitude'       => $lng
        ],
        ['%d','%s','%s','%s','%s','%f','%f']
    );

    if ($result) {
        $address_id = $wpdb->insert_id;
        wp_send_json([
            'success' => true,
            'message' => 'آدرس با موفقیت ثبت شد',
            'address_id' => $address_id,
            'user_id' => $user_id,
            'address_name' => $address_name,
            'latitude'        => $lat,
            'longitude'       => $lng
        ]);
    } else {
        wp_send_json(['success' => false, 'message' => 'خطا در ثبت آدرس']);
    }
}

//load map
function vos_enqueue_leaflet()
{
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);
    if (is_page('دامپزشک-سیار')) {
        wp_enqueue_script('vos-map', VOS_URL . 'assets/js/vos-map.js', ['leaflet-js', 'jquery'], null, true);
    }
}

add_action('wp_enqueue_scripts', 'vos_enqueue_leaflet');

//set Token
add_action('init', 'vos_auto_login_via_token');
function vos_auto_login_via_token()
{
    if (!is_user_logged_in() && !empty($_COOKIE['vos_token'])) {
        $token = sanitize_text_field($_COOKIE['vos_token']);
        $user_id = get_transient('vos_user_token_' . $token);

        if ($user_id) {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);
            do_action('wp_login', get_userdata($user_id)->user_login, get_userdata($user_id));
        }
    }
}

//cros-origin
add_action('init', 'vos_allow_cors');
function vos_allow_cors()
{
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $allowed = [
            'http://127.0.0.1:5500',
            'https://stage-webapp.petboom.co',
        ];
        if (in_array($_SERVER['HTTP_ORIGIN'], $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }
}

//otp

function vos_send_sms_via_digits($mobile, $otp = null)
{


    if (!get_option('digits_gateway_settings')) {
        $missing[] = 'Digits gateway settings missing';
    }

    // اگر صفر اول باشد، برداریم و 98 بچسبانیم
    if (preg_match('/^0\d{10}$/', $mobile)) {
        $mobile = '98' . ltrim($mobile, '0');
    }

    // تابع رسمی Digits
    if (function_exists('digit_send_otp')) {
        // country_code, mobile, otp, type, gateway, template_id
        return digit_send_otp('98', $mobile, $otp, 'otp', '', '');
    }

    return new WP_Error('digits_missing', 'تابع digit_send_otp پیدا نشد.');
}


// Add a debug hook to see what's happening
add_action('wp_ajax_nopriv_vos_send_otp', function () {
    error_log('VOS_DEBUG: wp_ajax_nopriv_vos_send_otp hook triggered');
}, 1);

add_action('wp_ajax_vos_send_otp', function () {
    error_log('VOS_DEBUG: wp_ajax_vos_send_otp hook triggered');
}, 1);


function vos_send_otp_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // Merge raw JSON body into $_POST (برای درخواست‌های fetch)
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // Nonce / token check (هر کدام موجود بود)
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';
    $ok = false;
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $ok = true;
    } elseif ($token && get_transient('vos_test_token_' . $token)) {
        $ok = true;
    }
    if (!$ok) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    // Mobile normalization
    $raw = vos_fa_to_en((string)($_POST['mobile'] ?? ''));
    $mobile = preg_replace('/\D+/', '', $raw);
    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'invalid mobile'], 422);
    }


    $missing = [];
    // نام کاملِ کلاس Digits
    if (!class_exists(\WEDEVS\DIGITS\Digits::class) && !function_exists('digit_send_otp')) {
        $missing[] = 'Digits plugin is not active';
    }
    // یکی از کلیدهای واقعی تنظیمات gateway در دیتابیس شما:
    $gateway = get_option('digit_whatsapp_gateway')
        ?: get_option('digit_custom_gateway')
            ?: get_option('digit_email_gateway');
    if (empty($gateway)) {
        $missing[] = 'Digits gateway settings missing';
    }

    if ($missing) {
        wp_send_json_error([
            'message' => implode(' – ', $missing)
        ], 500);
    }
    // Try to send SMS via different methods
    $sms_sent = false;
    $sms_error = '';


    if (function_exists('digit_send_otp')) {
        $resp = digit_send_otp('98', $mobile, null, 'sms_otp', '', '');
        if (!is_wp_error($resp) && $resp !== false) {
            // موفق شدیم: مستقیم پاسخ موفق می‌دهیم و ادامه ندهیم
            wp_send_json_success([
                'code' => 'otp_sent',
                'message' => 'OTP توسط Digits ارسال شد (DEFAULT gateway)',
                'mobile' => $mobile,
                'expires' => 300,
            ]);
            return; // اینجا از تابع خارج می‌شویم
        } else {
            // ذخیره خطا برای دیباگ داخلی
            $sms_error = is_wp_error($resp)
                ? $resp->get_error_message()
                : 'returned false when using DEFAULT gateway';
            error_log('DEFAULT gateway failed: ' . $sms_error);
        }
    }
// اگر موفق بودیم، بقیه‌ی روش‌ها را امتحان نمی‌کنیم
//    if ( $sms_sent ) {
//        error_log('SMS sent via default gateway to ' . $mobile);
//        wp_send_json_success([
//            'code'    => 'otp_sent',
//            'message' => 'OTP با موفقیت ارسال شد',
//            'mobile'  => $mobile,
//            'expires' => 300,
//        ]);
//        return;  // immediately exit the function
//    }

    // Method 1: Use Digits class if available
    if (class_exists('Digits')) {
        try {
            $digits = Digits::get_instance();
            $resp = $digits->send_otp([
                'mobile' => $mobile,
                'otpLength' => 4,
            ]);
            if (!is_wp_error($resp) && $resp !== false) {
                $sms_sent = true;
                $sms_error = 'Digits class succeeded';
            } else {
                $sms_error = is_wp_error($resp) ? $resp->get_error_message() : 'Digits returned false';
            }
        } catch (Exception $e) {
            $sms_error = 'Digits class error: ' . $e->getMessage();
            error_log($sms_error);
        }
    }

    // Method 2: Use digit_send_otp function if available
    if (!$sms_sent && function_exists('digit_send_otp')) {
        // Let Digits generate OTP automatically
        $methods = [
            ['98', $mobile, '', 'sms_otp', '', ''],
            ['98', $mobile, '', 'otp', '', ''],
            ['98', $mobile, '', 'sms_otp', 'abzarwp_ippanel', ''],
            ['98', $mobile, '', 'otp', 'abzarwp_ippanel', ''],
            ['98', $mobile, '', 'pattern', 'abzarwp_ippanel', ''],
            ['98', $mobile, '', 'sms_otp', 'ippanel', ''],
            ['98', $mobile, '', 'otp', 'ippanel', ''],
            ['98', $mobile, '', 'pattern', 'ippanel', '']
        ];

        foreach ($methods as $method) {
            $resp = call_user_func_array('digit_send_otp', $method);
            if (!is_wp_error($resp) && $resp !== false) {
                $sms_sent = true;
                $sms_error = 'digit_send_otp succeeded with method: ' . implode(',', $method);
                break;
            } else {
                $sms_error = is_wp_error($resp) ? $resp->get_error_message() : 'digit_send_otp returned false for method: ' . implode(',', $method);
            }
        }
    }

    // Method 3: Use digits_send_sms function if available
    if (!$sms_sent && function_exists('digits_send_sms')) {
        $resp = digits_send_sms('کد تایید شما', $mobile);
        if (!is_wp_error($resp) && $resp !== false) {
            $sms_sent = true;
            $sms_error = 'digits_send_sms succeeded';
        } else {
            $sms_error = is_wp_error($resp) ? $resp->get_error_message() : 'digits_send_sms returned false';
        }
    }

    // Method 4: Use vos_send_sms function if available
    if (!$sms_sent && function_exists('vos_send_sms')) {
        $resp = vos_send_sms('کد تایید شما', $mobile);
        if (!is_wp_error($resp) && $resp !== false) {
            $sms_sent = true;
            $sms_error = 'vos_send_sms succeeded';
        } else {
            $sms_error = is_wp_error($resp) ? $resp->get_error_message() : 'vos_send_sms returned false';
        }
    }

    // If no SMS gateway available, log the error
    if (!$sms_sent) {
        error_log('SMS sending failed for mobile ' . $mobile . '. Error: ' . $sms_error);

        // Check if Digits is properly configured
        $digits_config = [
            'digits_general_settings' => get_option('digits_general_settings'),
            'digits_gateway_settings' => get_option('digits_gateway_settings'),
            'digits_abzarwp_ippanel' => get_option('digits_abzarwp_ippanel'),
            'digits_ippanel' => get_option('digits_ippanel'),
        ];

        error_log('Digits configuration: ' . print_r($digits_config, true));

        // For testing, we'll still return success but log the issue
    }

    // Log successful SMS sending
//    if ( $sms_sent ) {
//        error_log('SMS sent successfully to ' . $mobile . ' with OTP: ' . $otp);
//    }
    if (!$sms_sent) {
        wp_send_json_error([
            'code' => 'sms_failed',
            'message' => 'ارسال پیامک ناموفق بود',
            'mobile' => $mobile,
            'sms_error' => $sms_error,
            'debug_info' => [
                'digits_class_exists' => class_exists('Digits'),
                'digit_send_otp_exists' => function_exists('digit_send_otp'),
                'digits_send_sms_exists' => function_exists('digits_send_sms'),
                'vos_send_sms_exists' => function_exists('vos_send_sms'),
                'digits_config' => [
                    'digit_general_settings' => get_option('digit_general_settings') ? 'exists' : 'not_exists',
                    'digit_gateway_settings' => get_option('digit_gateway_settings') ? 'exists' : 'not_exists',
                    'digit_abzarwp_ippanel' => get_option('digit_abzarwp_ippanel') ? 'exists' : 'not_exists',
                    'digit_ippanel' => get_option('digit_ippanel') ? 'exists' : 'not_exists'
                ]
            ]
        ]);
    }
    // Success! OTP sent by Digits
    wp_send_json_success([
        'code' => 'otp_sent',
        'message' => 'OTP توسط Digits ارسال شد',
        'mobile' => $mobile,

        'expires' => 300,
        'sms_sent' => $sms_sent,
        'sms_error' => $sms_error,
        'debug_info' => [
            'digits_class_exists' => class_exists('Digits'),
            'digit_send_otp_exists' => function_exists('digit_send_otp'),
            'digits_send_sms_exists' => function_exists('digits_send_sms'),
            'vos_send_sms_exists' => function_exists('vos_send_sms'),
            'digits_config' => [
                'digit_general_settings' => get_option('digit_general_settings') ? 'exists' : 'not_exists',
                'digit_gateway_settings' => get_option('digit_gateway_settings') ? 'exists' : 'not_exists',
                'digit_abzarwp_ippanel' => get_option('digit_abzarwp_ippanel') ? 'exists' : 'not_exists',
                'digit_ippanel' => get_option('digit_ippanel') ? 'exists' : 'not_exists'
            ]
        ]
    ]);
}

add_action('wp_ajax_nopriv_vos_send_otp', 'vos_send_otp_ajax');
add_action('wp_ajax_vos_send_otp', 'vos_send_otp_ajax');

// -----------------------------------------------
// 2) Verify OTP
// -----------------------------------------------
function vos_verify_otp_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // Merge raw JSON body into $_POST (برای درخواست‌های fetch)
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // Nonce / token check
    $nonce = $_POST['_ajax_nonce'] ?? '';
    $token = $_POST['_token'] ?? '';
    $ok = false;
    if ($nonce && check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false)) {
        $ok = true;
    } elseif ($token && get_transient('vos_test_token_' . $token)) {
        $ok = true;
    }
    if (!$ok) {
        wp_send_json_error(['message' => 'invalid nonce or token'], 400);
    }

    // Read & sanitize params
    $raw = vos_fa_to_en((string)($_POST['mobile'] ?? ''));
    $mobile = preg_replace('/\D+/', '', $raw);
    $otp = preg_replace('/\D+/', '', (string)($_POST['otp'] ?? ''));

    if (!preg_match('/^09\d{9}$/', $mobile)) {
        wp_send_json_error(['message' => 'invalid mobile'], 422);
    }
    if (strlen($otp) < 4) {
        wp_send_json_error(['message' => 'invalid otp'], 422);
    }

    // Verify OTP using Digits
    if (!class_exists('Digits') && !function_exists('digit_send_otp')) {
        wp_send_json_error(['message' => 'digits_not_configured'], 500);
    }

    // Try different methods to verify OTP
    $verify = false;

    // Method 1: Use Digits class if available
    if (class_exists('Digits')) {
        try {
            $digits = Digits::get_instance();
            $verify = $digits->verify_otp([
                'mobile' => $mobile,
                'otp' => $otp,
            ]);
        } catch (Exception $e) {
            error_log('Digits class error: ' . $e->getMessage());
        }
    }

    // Method 2: Use digit_verify_otp function if available
    if (!$verify && function_exists('digit_verify_otp')) {
        $verify = digit_verify_otp($mobile, $otp);
    }

    // Method 3: No fallback - let it fail if Digits doesn't work
    if (!$verify) {
        // Digits verification failed
        $verify = false;
    }

    // Check verification result
    if (is_wp_error($verify)) {
        wp_send_json_error([
            'message' => 'otp_invalid',
            'error' => $verify->get_error_message(),
        ], 400);
    }
    if ($verify === false) {
        wp_send_json_error(['message' => 'otp_invalid_or_expired'], 400);
    }

    // Success – mark user/session verified as needed
    wp_send_json_success([
        'code' => 'otp_verified',
        'message' => 'OTP صحیح است',
        'mobile' => $mobile,
    ]);
}

add_action('wp_ajax_nopriv_vos_verify_otp', 'vos_verify_otp_ajax');
add_action('wp_ajax_vos_verify_otp', 'vos_verify_otp_ajax');


// تابع تست تأیید OTP
function vos_test_verify_otp($mobile, $otp)
{
    // Verify OTP using Digits
    if (!class_exists('Digits') && !function_exists('digit_send_otp')) {
        return [
            'success' => false,
            'error' => 'Digits پلاگین فعال نیست',
            'mobile' => $mobile,
            'otp' => $otp
        ];
    }

    // Try different methods to verify OTP
    $verify = false;

    // Method 1: Use Digits class if available
    if (class_exists('Digits')) {
        try {
            $digits = Digits::get_instance();
            $verify = $digits->verify_otp([
                'mobile' => $mobile,
                'otp' => $otp,
            ]);
        } catch (Exception $e) {
            error_log('Digits class error: ' . $e->getMessage());
        }
    }

    // Method 2: Use digit_verify_otp function if available
    if (!$verify && function_exists('digit_verify_otp')) {
        $verify = digit_verify_otp($mobile, $otp);
    }

    // Method 3: No fallback - let it fail if Digits doesn't work
    if (!$verify) {
        // Digits verification failed
        $verify = false;
    }

    // Check verification result
    if (is_wp_error($verify)) {
        return [
            'success' => false,
            'error' => $verify->get_error_message(),
            'mobile' => $mobile,
            'otp' => $otp
        ];
    }

    if ($verify === false) {
        return [
            'success' => false,
            'error' => 'OTP نامعتبر یا منقضی شده است',
            'mobile' => $mobile,
            'otp' => $otp
        ];
    }

    return [
        'success' => true,
        'is_valid' => true,
        'mobile' => $mobile,
        'otp' => $otp,
        'message' => 'OTP معتبر است'
    ];
}

// تابع اصلاح تنظیمات IPPanel
function vos_fix_ippanel_ajax()
{
    header('Content-Type: application/json; charset=utf-8');

    // ادغام JSON با $_POST
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $_POST = array_merge($_POST, $body);
    }

    // بررسی توکن
    $token = $_POST['_token'] ?? '';
    if (!$token || !get_transient('vos_test_token_' . $token)) {
        wp_send_json_error(['message' => 'invalid token'], 400);
    }

    // دریافت تنظیمات فعلی IPPanel
    $ippanel_settings = get_option('digit_abzarwp_ippanel', []);

    if (empty($ippanel_settings)) {
        wp_send_json_error(['message' => 'IPPanel settings not found'], 404);
    }

    // اصلاح شماره فرستنده
    $ippanel_settings['from'] = '+9810002040006050';

    // ذخیره تنظیمات جدید
    $updated = update_option('digit_abzarwp_ippanel', $ippanel_settings);

    if ($updated) {
        wp_send_json_success([
            'message' => 'IPPanel settings updated successfully',
            'old_from' => $ippanel_settings['from'] ?? 'not_set',
            'new_from' => '+9810002040006050',
            'settings' => $ippanel_settings
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to update IPPanel settings'], 500);
    }
}


if (!function_exists('vos_send_sms')) {
    function vos_send_sms(string $msg, string $mobile)
    {
        // ➊ شماره را به فرمت بین‌المللی 98912… ببریم
        if (preg_match('/^0\d{10}$/', $mobile)) {
            $mobile = '98' . ltrim($mobile, '0');
        }

        /* === Digits 8/9: تابع digit_send_otp دارای 6 پارامتر === */
        if (function_exists('digit_send_otp')) {
            try {
                $ref = new ReflectionFunction('digit_send_otp');
                $argc = $ref->getNumberOfParameters();   // معمولاً 6

                // ترتیب واقعی پارامترها را استخراج کنیم
                $params = array_map(fn($p) => $p->getName(), $ref->getParameters());

                /*
                   امضای رایج نسخه 8/9:
                   digit_send_otp( $country_code, $mobile, $otp, $type, $gateway, $template_id )
                   مثال امن: country_code=98, otp=null (Digits خودش OTP می‌سازد), type='otp'
                */
                $payload = [
                    'country_code' => '98',
                    'mobile' => $mobile,
                    'otp' => null,
                    'type' => 'otp',
                    'gateway' => '',
                    'template_id' => '',
                ];

                // مرتب‌سازی به همان ترتیبی که تابع انتظار دارد
                $args = [];
                foreach ($params as $p) {
                    $args[] = $payload[$p] ?? null;
                }

                return call_user_func_array('digit_send_otp', $args);

            } catch (ReflectionException $e) {
                return new WP_Error('digits_reflect', $e->getMessage());
            }
        }

        /* === Digits ≤7: تابع digits_send_sms === */
        if (function_exists('digits_send_sms')) {
            return digits_send_sms($msg, $mobile);
        }

        /* === Digits کلاس محور (بعضی نسخه‌های سفارشی) === */
        if (class_exists('Digits_Sender')) {
            return Digits_Sender::getInstance()->sendSMS($msg, $mobile);
        }

        /* === fallback: Persian Woo SMS یا هر درگاه دیگر === */
        if (function_exists('pwsms_send')) {
            return pwsms_send($mobile, $msg);
        }


        return new WP_Error('no_sms_gateway', 'هیچ تابع ارسال SMS پیدا نشد');
    }
}
add_action('wp_ajax_send_otp_for_user', 'send_otp_for_user');
add_action('wp_ajax_nopriv_send_otp_for_user', 'send_otp_for_user');
function send_otp_for_user()
{
    if (function_exists('digit_send_otp')) {
        $mobile = $_POST['mobile'];
        $countrycode = '98';

        $result = digit_send_otp($countrycode, $mobile, null, 'sms_otp', 'abzarwp_ippanel', '');

        if (is_array($result) && !empty($result['success'])) {
            wp_send_json_success(['message' => 'OTP ارسال شد.']);
        } else {
            wp_send_json_error(['message' => 'خطا در ارسال OTP.', 'debug' => $result]);
        }
    }
    wp_die();
}

add_action('wp_ajax_verify_user_otp', 'verify_user_otp');
add_action('wp_ajax_nopriv_verify_user_otp', 'verify_user_otp');
function verify_user_otp()
{
    if (function_exists('digit_verify_otp')) {
        $mobile = $_POST['mobile'];
        $otp = $_POST['otp'];
        $countrycode = '98';

        $verify = digit_verify_otp($countrycode, $mobile, $otp, 'sms_otp');

        if (is_array($verify) && !empty($verify['success'])) {
            wp_send_json_success(['message' => 'تایید شد.']);
        } else {
            wp_send_json_error(['message' => 'کد اشتباه است.']);
        }
    }
    wp_die();
}

add_action('wp_ajax_nopriv_send_my_otp', 'send_my_otp');
add_action('wp_ajax_send_my_otp', 'send_my_otp');

function send_my_otp()
{
    $mobile = $_POST['mobile'];
    $otp = rand(1000, 9999); // تولید کد OTP

    // ذخیره کد OTP برای کاربر (مثلاً در transient یا user_meta یا جدول خودت)
    set_transient('otp_' . $mobile, $otp, 10 * MINUTE_IN_SECONDS);

    // ارسال پیامک با Persian WooCommerce SMS یا wp-parsi-sms یا کد gateway مستقیم
    if (function_exists('pw_iran_send_sms')) {
        pw_iran_send_sms($mobile, "کد ورود شما: $otp");
    }
    // اگر gateway اختصاصی داری از همون استفاده کن

    wp_send_json_success(['message' => 'کد تایید ارسال شد']);
}

add_action('wp_ajax_nopriv_verify_my_otp', 'verify_my_otp');
add_action('wp_ajax_verify_my_otp', 'verify_my_otp');

function verify_my_otp()
{
    $mobile = $_POST['mobile'];
    $otp = $_POST['otp'];
    $stored_otp = get_transient('otp_' . $mobile);

    if ($stored_otp && $stored_otp == $otp) {
        // موفقیت!
        delete_transient('otp_' . $mobile); // یکبار مصرف
        wp_send_json_success(['message' => 'کد تایید شد']);
    } else {
        wp_send_json_error(['message' => 'کد اشتباه است']);
    }
}

add_action('init', function () {
    add_shortcode('digits-login', 'digits_render_login_form');
});

function vos_verify_mobile()
{
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);
    $user = get_user_by('login', $mobile);

    if (!$user) {
        $query = new WP_User_Query([
            'meta_key' => 'billing_phone',
            'meta_value' => $mobile
        ]);
        $users = $query->get_results();
        if (!empty($users)) {
            $user = $users[0];
        }
    }

    if ($user) {
        wp_send_json_success([
            'valid' => true,
            'user_id' => $user->ID,
            'mobile' => $mobile,
        ]);
    } else {
        wp_send_json_success([
            'valid' => false,
            'message' => 'این شماره در سیستم ثبت نشده است.',
        ]);
    }
}

add_action('wp_ajax_vos_verify_mobile', 'vos_verify_mobile');
add_action('wp_ajax_nopriv_vos_verify_mobile', 'vos_verify_mobile');

function vos_register_mobile()
{
    $name = sanitize_text_field($_POST['name']);
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);

    if (empty($name) || empty($mobile)) {
        wp_send_json_error(['message' => 'اطلاعات کافی نیست']);
    }

    // چک کنید که قبلاً وجود ندارد
    if (get_user_by('login', $mobile) || count((new WP_User_Query(['meta_key' => 'billing_phone', 'meta_value' => $mobile]))->get_results())) {
        wp_send_json_error(['message' => 'این شماره قبلاً ثبت شده است.']);
    }

    // ساخت یوزر جدید
    $random_pass = wp_generate_password(12, false);
    $user_id = wp_create_user($mobile, $random_pass, $mobile . '@example.com');
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'خطا در ایجاد کاربر.']);
    }

    // ست کردن نام و متا
    wp_update_user([
        'ID' => $user_id,
        'display_name' => $name,
        'first_name' => $name,
    ]);
    update_user_meta($user_id, 'billing_phone', $mobile);

    // (در صورت نیاز می‌توانید ایمیل خوش‌آمدگویی ارسال کنید)

    wp_send_json_success([
        'user_id' => $user_id
    ]);
}

add_action('wp_ajax_vos_register_mobile', 'vos_register_mobile');
add_action('wp_ajax_nopriv_vos_register_mobile', 'vos_register_mobile');


// تولید و ارسال OTP به موبایل (مثلاً با Twilio یا هر سرویس SMS)
function vos_send_otp($mobile)
{
    $otp = rand(100000, 999999);
    // ذخیره در ترنزیِنت به مدت 5 دقیقه
    set_transient("vos_otp_{$mobile}", $otp, 5 * MINUTE_IN_SECONDS);
    // TODO: اینجا SMS بفرستید:
    // send_sms( $mobile, "کد تأیید شما: $otp" );
}

add_action('wp_ajax_vos_send_login_otp', 'vos_send_login_otp');
add_action('wp_ajax_nopriv_vos_send_login_otp', 'vos_send_login_otp');
function vos_send_login_otp()
{
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);
    if (empty($mobile)) {
        wp_send_json_error(['message' => 'شماره وارد نشده']);
    }
    // مطمئن شوید شماره در سیستم هست
    $user = get_user_by('login', $mobile) ?: (new WP_User_Query([
        'meta_key' => 'billing_phone', 'meta_value' => $mobile
    ]))->get_results()[0] ?? null;

    if (!$user) {
        wp_send_json_error(['message' => 'این شماره در سیستم ثبت نشده']);
    }
    vos_send_otp($mobile);
    wp_send_json_success(['message' => 'کد OTP ارسال شد']);
}

add_action('wp_ajax_vos_verify_login_otp', 'vos_verify_login_otp');
add_action('wp_ajax_nopriv_vos_verify_login_otp', 'vos_verify_login_otp');
function vos_verify_login_otp()
{
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);
    $code = sanitize_text_field($_POST['otp']);
    $saved = get_transient("vos_otp_{$mobile}");

    if ($saved && $code == $saved) {
        delete_transient("vos_otp_{$mobile}");
        // بازگرداندن user_id
        $user = get_user_by('login', $mobile) ?: (new WP_User_Query([
            'meta_key' => 'billing_phone', 'meta_value' => $mobile
        ]))->get_results()[0] ?? null;
        wp_send_json_success([
            'user_id' => $user->ID,
            'mobile' => $mobile
        ]);
    } else {
        wp_send_json_error(['message' => 'کد اشتباه است']);
    }
}

// ثبت‌نام: ارسال OTP
add_action('wp_ajax_vos_send_register_otp', 'vos_send_register_otp');
add_action('wp_ajax_nopriv_vos_send_register_otp', 'vos_send_register_otp');
function vos_send_register_otp()
{
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);
    $email = sanitize_email($_POST['email']);
    if (empty($mobile) || empty($email)) {
        wp_send_json_error(['message' => 'موبایل یا ایمیل وارد نشده']);
    }
    // چک یکتا بودن موبایل و ایمیل
    if (username_exists($mobile) || email_exists($email)) {
        wp_send_json_error(['message' => 'این موبایل یا ایمیل قبلاً ثبت شده']);
    }
    vos_send_otp($mobile);
    // برای ثبت‌نام بعداً نام را هم ارسال می‌کنیم
    wp_send_json_success(['message' => 'کد OTP ارسال شد']);
}

add_action('wp_ajax_vos_verify_register_otp', 'vos_verify_register_otp');
add_action('wp_ajax_nopriv_vos_verify_register_otp', 'vos_verify_register_otp');
function vos_verify_register_otp()
{
    $mobile = preg_replace('/\D+/', '', $_POST['mobile']);
    $email = sanitize_email($_POST['email']);
    $name = sanitize_text_field($_POST['name']);
    $code = sanitize_text_field($_POST['otp']);
    $saved = get_transient("vos_otp_{$mobile}");

    if (!($saved && $code == $saved)) {
        wp_send_json_error(['message' => 'کد اشتباه است']);
    }
    delete_transient("vos_otp_{$mobile}");

    // ایجاد کاربر
    $pass = wp_generate_password(12, false);
    $user_id = wp_create_user($mobile, $pass, $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'خطا در ایجاد کاربر']);
    }
    wp_update_user([
        'ID' => $user_id,
        'display_name' => $name,
        'first_name' => $name
    ]);
    update_user_meta($user_id, 'billing_phone', $mobile);

    wp_send_json_success([
        'user_id' => $user_id,
        'mobile' => $mobile
    ]);
}

//add_action('wp_ajax_load_digits_shortcode', 'vos_load_digits_shortcode');
//add_action('wp_ajax_nopriv_load_digits_shortcode', 'vos_load_digits_shortcode');
//function vos_load_digits_shortcode()
//{
//    error_log("🛠 vos_load_digits_shortcode fired");
//
//    if (!shortcode_exists('dm-page')) {
//        wp_send_json_error(['message' => 'Shortcode not found']);
//    }
//
////    ob_start();
////    $out = do_shortcode('[dm-page]');
////    error_log('Digits Shortcode Output: ' . strip_tags($out));
//   $html = do_shortcode( '[dm-page]' );
//
//    if (trim($html) === '[dm-page]') {
//        wp_send_json_error( [ 'message' => 'Shortcode rendered as literal' ] );
//    }
//
//    wp_send_json_success( [ 'html' => $html ] );
//    }


add_action('wp_enqueue_scripts', function () {
    $digits_plugin = WP_PLUGIN_DIR . '/digits/digits.php';
    if (file_exists($digits_plugin)) {
        $digits_url = plugins_url('assets/js/', $digits_plugin);
        wp_enqueue_script('digits-main', $digits_url . 'main.min.js', ['jquery'], null, true);
        wp_enqueue_script('digits-login', $digits_url . 'login.min.js', ['jquery'], null, true);
        wp_enqueue_script('digits-script', $digits_url . 'script.min.js', ['jquery'], null, true);
    }
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'digits-main',
        plugins_url('assets/js/main.min.js', 'digits/digits.php'),
        ['jquery'],
        null,
        true
    );
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'digits-login',
        plugins_url('assets/js/login.min.js', 'digits/digits.php'),
        ['jquery'],
        null,
        true
    );
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'vos-digits-integration',
        plugins_url('assets/js/digits-integration.js', __FILE__),
        ['jquery'],
        null,
        true
    );
    wp_localize_script('vos-digits-integration', 'VOS', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'url' => VOS_URL,
        'nonces' => [
            'dm' => wp_create_nonce('vos_dm_nonce'),
        ],
    ]);
});


//addresses
add_action( 'wp_ajax_vos_get_addresses',        'vos_get_addresses' );
add_action( 'wp_ajax_nopriv_vos_get_addresses', 'vos_get_addresses' ); // اگر برای مهمان لازم نیست، این را بردار

function vos_get_addresses() {
    // فقط کاربران لاگین‌‌شده
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'not_logged_in' ], 403 );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_addresses';   // نام جدول خودت
    $user_id = get_current_user_id();

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, address_name, address_city, address_province, latitude, longitude, address_dl, created_at
             FROM $table
             WHERE user_id = %d
             ORDER BY created_at DESC",          // یا ORDER BY id DESC
            $user_id
        ),
        ARRAY_A
    );

    wp_send_json_success( [ 'addresses' => $rows ] );
}

add_action('wp_ajax_nopriv_vos_digits_login', 'vos_digits_login');
add_action('wp_ajax_vos_digits_login', 'vos_digits_login');
function vos_digits_login() {
    header('Content-Type: application/json; charset=utf-8');

    $nonce = $_POST['_ajax_nonce'] ?? '';
    if ( ! check_ajax_referer('vos_phone_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error(['message' => 'invalid_nonce'], 403);
    }

    $raw    = vos_fa_to_en((string)($_POST['mobile'] ?? ''));
    $mobile = preg_replace('/\D+/', '', $raw);
    if ( ! preg_match('/^09\d{9}$/', $mobile) ) {
        wp_send_json_error(['message' => 'invalid_mobile'], 400);
    }

    $otp = isset($_POST['otp']) ? preg_replace('/\D+/', '', (string)$_POST['otp']) : '';

    if ($otp === '') {
        $sent = vos_send_digits_otp($mobile);
        if ($sent === true) {
            wp_send_json_success(['message' => 'کد تایید ارسال شد.']);
        } elseif (is_wp_error($sent)) {
            wp_send_json_error(['message' => $sent->get_error_message()], 500);
        } else {
            wp_send_json_error(['message' => 'خطا در ارسال کد تایید.'], 500);
        }
    }

    $verified = vos_verify_digits_otp($mobile, $otp);
    if (is_wp_error($verified) || ! $verified) {
        wp_send_json_error(['message' => 'کد تایید اشتباه است.'], 400);
    }

    $user = get_user_by('login', $mobile);
    if ( ! $user ) {
        $user_id = vos_create_user_from_mobile($mobile);
        if ( ! $user_id ) {
            wp_send_json_error(['message' => 'user_not_found'], 500);
        }
    } else {
        $user_id = $user->ID;
    }

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_send_json_success([
        'message'   => 'ورود موفقیت‌آمیز بود!',
        'user_id'   => $user_id,
        'phone'     => $mobile,
        'logged_in' => true,
    ]);
}

function vos_send_digits_otp($mobile) {
    if (class_exists('Digits')) {
        try {
            $digits = Digits::get_instance();
            $resp   = $digits->send_otp(['mobile' => $mobile]);
            if ( ! is_wp_error($resp) && $resp !== false) {
                return true;
            }
            return is_wp_error($resp) ? $resp : new WP_Error('digits_send_failed', 'Digits send otp failed');
        } catch (Exception $e) {
            return new WP_Error('digits_exception', $e->getMessage());
        }
    }

    if (function_exists('digit_send_otp')) {
        $resp = digit_send_otp('98', $mobile, '', 'sms_otp', '', '');
        if ( ! is_wp_error($resp) && $resp !== false) {
            return true;
        }
        return is_wp_error($resp) ? $resp : new WP_Error('digits_send_failed', 'digit_send_otp failed');
    }

    return new WP_Error('digits_not_available', 'Digits not configured');
}

function vos_verify_digits_otp($mobile, $otp) {
    if (class_exists('Digits')) {
        try {
            $digits = Digits::get_instance();
            $verify = $digits->verify_otp([
                'mobile' => $mobile,
                'otp'    => $otp,
            ]);
            if (is_wp_error($verify) || ! $verify) {
                return is_wp_error($verify) ? $verify : false;
            }
            return true;
        } catch (Exception $e) {
            return new WP_Error('digits_exception', $e->getMessage());
        }
    }

    if (function_exists('digit_verify_otp')) {
        $verify = digit_verify_otp($mobile, $otp);
        return $verify;
    }

    return new WP_Error('digits_not_available', 'Digits not configured');
}

function vos_create_user_from_mobile($mobile) {
    $existing = get_user_by('login', $mobile);
    if ($existing) {
        return $existing->ID;
    }

    $password = wp_generate_password(12, false);
    $email    = $mobile . '@example.com';
    $user_id  = wp_create_user($mobile, $password, $email);
    if (is_wp_error($user_id)) {
        return 0;
    }

    update_user_meta($user_id, 'digits_phone', $mobile);
    return $user_id;
}