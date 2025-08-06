<?php
///**
// * Load Digits Shortcode via AJAX
// */
//
//// Include WordPress
//require_once('../../../wp-load.php');
//
//// Set headers for AJAX
//header('Content-Type: application/json; charset=utf-8');
//
//// Check if this is an AJAX request
//if (!wp_doing_ajax()) {
//    wp_send_json_error('Invalid request');
//}
//
//// Get the shortcode output
//$shortcode_output = '';
//$error_message = '';
//
//try {
//    if (function_exists('do_shortcode') && shortcode_exists('digits-login')) {
//        $shortcode_output = do_shortcode('[digits-login]');
//
//        // Check if shortcode actually produced output
//        if (empty($shortcode_output) || $shortcode_output === '[digits-login]') {
//            $error_message = 'Shortcode produced no output';
//        }
//    } else {
//        $error_message = 'Digits plugin not available';
//    }
//} catch (Exception $e) {
//    $error_message = $e->getMessage();
//}
//
//// Return response
//if (!empty($shortcode_output) && $shortcode_output !== '[digits_login]') {
//    wp_send_json_success([
//        'html' => $shortcode_output,
//        'message' => 'Shortcode loaded successfully'
//    ]);
//} else {
//    wp_send_json_error([
//        'message' => $error_message ?: 'Failed to load shortcode',
//        'fallback_html' => '<div class="digits-fallback-form">
//            <p>برای ادامه، لطفاً وارد سایت شوید:</p>
//            <div class="digits-form">
//                <input type="tel" name="mobile" placeholder="شماره موبایل" maxlength="11" />
//                <button type="button" id="mobile-login-btn">ادامه</button>
//            </div>
//        </div>'
//    ]);
//}