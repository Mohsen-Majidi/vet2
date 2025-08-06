# راهنمای تست API با Postman

## 1. دریافت نانس
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_get_nonce
```

## 2. بررسی وضعیت Digits
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_check_digits
_ajax_nonce: {nonce_from_step_1}
```

## 3. بررسی تنظیمات IPPanel
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_check_ippanel
_ajax_nonce: {nonce_from_step_1}
```

## 4. بررسی درگاه فعال
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_check_active_gateway
_ajax_nonce: {nonce_from_step_1}
```

## 5. تست مستقیم درگاه SMS
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_test_sms_gateway
_ajax_nonce: {nonce_from_step_1}
mobile: 09123456789
message: تست درگاه SMS
```

## 6. دیباگ تنظیمات IPPanel
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_debug_ippanel
_ajax_nonce: {nonce_from_step_1}
```

## 7. فعال کردن درگاه IPPanel
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_activate_ippanel
_ajax_nonce: {nonce_from_step_1}
```

## 8. تست مستقیم IPPanel
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_test_ippanel_direct
_ajax_nonce: {nonce_from_step_1}
mobile: 09123856521
message: تست مستقیم IPPanel
```

## 9. تست مستقیم Digits
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_test_digits
_ajax_nonce: {nonce_from_step_1}
```

## 10. ارسال OTP
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_send_otp
_ajax_nonce: {nonce_from_step_1}
mobile: 09123456789
```

## 11. تأیید OTP
```
POST: {your-site}/wp-admin/admin-ajax.php
Action: vos_verify_otp
_ajax_nonce: {nonce_from_step_1}
mobile: 09123456789
otp: 1234
```

## نکات مهم:
- `{your-site}` را با آدرس سایت خود جایگزین کنید
- `{nonce_from_step_1}` را با نانس دریافتی از مرحله 1 جایگزین کنید
- تمام درخواست‌ها باید با `Content-Type: application/x-www-form-urlencoded` ارسال شوند 