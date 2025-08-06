# راهنمای کامل تست API لاگین موبایل در Postman

## اطلاعات کلی
- **Base URL**: `https://your-domain.com/wp-admin/admin-ajax.php`
- **Method**: `POST`
- **Content-Type**: `application/x-www-form-urlencoded`

## مرحله 1: دریافت نانس یا توکن

### گزینه 1: دریافت نانس (برای استفاده در مرورگر)
```
GET https://your-domain.com/wp-admin/admin-ajax.php?action=vos_get_nonce
```

**نمونه پاسخ:**
```json
{
  "success": true,
  "data": {
    "nonce": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
    "timestamp": 1703123456,
    "expires_in": 86400
  }
}
```

### گزینه 2: دریافت توکن (برای تست در Postman)
```
GET https://your-domain.com/wp-admin/admin-ajax.php?action=vos_get_token
```

**نمونه پاسخ:**
```json
{
  "success": true,
  "data": {
    "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6",
    "timestamp": 1703123456,
    "expires_in": 86400
  }
}
```

## مرحله 2: تست لاگین موبایل

### درخواست POST
```
POST https://your-domain.com/wp-admin/admin-ajax.php
```

### Headers
```
Content-Type: application/x-www-form-urlencoded
```

### Body (x-www-form-urlencoded)

#### با نانس:
```
action: check_mobile_digits
mobile: 09123456789
_ajax_nonce: YOUR_NONCE_HERE
```

#### با توکن:
```
action: check_mobile_digits
mobile: 09123456789
_token: YOUR_TOKEN_HERE
```

## راهنمای کامل Postman

### مرحله 1: ایجاد Collection
1. Postman را باز کنید
2. روی "New" کلیک کنید
3. "Collection" را انتخاب کنید
4. نام آن را "VetOnSite API" بگذارید

### مرحله 2: دریافت توکن
1. روی "Add request" کلیک کنید
2. نام: "Get Token"
3. Method: `GET`
4. URL: `https://your-domain.com/wp-admin/admin-ajax.php?action=vos_get_token`
5. Send را بزنید
6. توکن را از پاسخ کپی کنید

### مرحله 3: تست لاگین
1. روی "Add request" کلیک کنید
2. نام: "Check Mobile"
3. Method: `POST`
4. URL: `https://your-domain.com/wp-admin/admin-ajax.php`

#### Headers:
```
Content-Type: application/x-www-form-urlencoded
```

#### Body:
- Type: `x-www-form-urlencoded`
- Key: `action`, Value: `check_mobile_digits`
- Key: `mobile`, Value: `09123456789`
- Key: `_token`, Value: `YOUR_TOKEN_FROM_STEP_2`

### مرحله 4: تست حالت‌های مختلف

#### تست شماره معتبر:
```
mobile: 09123456789
```

#### تست شماره با اعداد فارسی:
```
mobile: ۰۹۱۲۳۴۵۶۷۸۹
```

#### تست شماره کوتاه:
```
mobile: 0912345678
```

#### تست شماره بلند:
```
mobile: 091234567890
```

#### تست شماره با پیشوند اشتباه:
```
mobile: 08123456789
```

#### تست شماره خالی:
```
mobile: 
```

## نمونه پاسخ‌های API

### 1. شماره معتبر و ثبت شده
```json
{
  "success": true,
  "data": {
    "ok": true,
    "code": "registered",
    "message": "شماره پیدا شد. می‌توانید ادامه دهید.",
    "phone": {
      "digits": "09123456789",
      "e164": "+989123456789"
    },
    "user_id": 123
  }
}
```

### 2. شماره معتبر اما ثبت نشده
```json
{
  "success": true,
  "data": {
    "ok": true,
    "code": "not_registered",
    "message": "این شماره ثبت نشده است.",
    "phone": {
      "digits": "09123456789",
      "e164": "+989123456789"
    },
    "user_id": null
  }
}
```

### 3. شماره خالی
```json
{
  "success": true,
  "data": {
    "ok": false,
    "code": "required",
    "message": "شماره موبایل را وارد کنید.",
    "user_id": null
  }
}
```

### 4. شماره با طول نامعتبر
```json
{
  "success": true,
  "data": {
    "ok": false,
    "code": "invalid_length",
    "message": "شماره باید دقیقاً ۱۱ رقم باشد.",
    "user_id": null
  }
}
```

### 5. شماره با پیشوند نامعتبر
```json
{
  "success": true,
  "data": {
    "ok": false,
    "code": "invalid_prefix",
    "message": "شماره باید با 09 شروع شود.",
    "user_id": null
  }
}
```

### 6. توکن نامعتبر
```json
{
  "success": false,
  "data": {
    "message": "invalid nonce or token"
  }
}
```

## کدهای خطا و پاسخ‌ها

| کد | توضیح | user_id |
|----|-------|---------|
| `required` | شماره موبایل وارد نشده | `null` |
| `invalid_length` | شماره دقیقاً ۱۱ رقم نیست | `null` |
| `invalid_prefix` | شماره با 09 شروع نمی‌شود | `null` |
| `registered` | شماره در سیستم ثبت شده | `عدد` |
| `not_registered` | شماره در سیستم ثبت نشده | `null` |

## ساختار کامل پاسخ‌ها

### پاسخ‌های موفق (ok: true)
```json
{
  "success": true,
  "data": {
    "ok": true,
    "code": "registered|not_registered",
    "message": "پیام مناسب",
    "phone": {
      "digits": "09123456789",
      "e164": "+989123456789"
    },
    "user_id": 123 // یا null برای not_registered
  }
}
```

### پاسخ‌های خطا (ok: false)
```json
{
  "success": true,
  "data": {
    "ok": false,
    "code": "required|invalid_length|invalid_prefix",
    "message": "پیام خطا",
    "user_id": null
  }
}
```

### پاسخ خطای امنیتی
```json
{
  "success": false,
  "data": {
    "message": "invalid nonce or token"
  }
}
```

## نکات مهم

1. **تبدیل اعداد فارسی**: API به طور خودکار اعداد فارسی را به انگلیسی تبدیل می‌کند
2. **حذف کاراکترهای اضافی**: تمام کاراکترهای غیر عددی حذف می‌شوند
3. **اعتبارسنجی**: شماره باید دقیقاً ۱۱ رقم و با 09 شروع شود
4. **امنیت**: حتماً از نانس یا توکن معتبر استفاده کنید
5. **Digits Integration**: API با پلاگین Digits سازگار است
6. **انقضای توکن**: توکن‌ها 24 ساعت معتبر هستند

## Environment Variables در Postman

برای راحتی، می‌توانید متغیرهای محیطی تعریف کنید:

1. روی "Environments" کلیک کنید
2. "New Environment" را انتخاب کنید
3. متغیرهای زیر را اضافه کنید:
   - `base_url`: `https://your-domain.com`
   - `token`: (توکن دریافتی)
   - `nonce`: (نانس دریافتی)

سپس در درخواست‌ها از `{{base_url}}` استفاده کنید.

## تست خودکار

می‌توانید یک Collection کامل با تمام تست‌ها ایجاد کنید:

1. Get Token
2. Check Valid Mobile (Registered)
3. Check Valid Mobile (Not Registered)
4. Check Invalid Length
5. Check Invalid Prefix
6. Check Empty Mobile
7. Check Invalid Token

هر تست را می‌توانید به عنوان یک Test Script اجرا کنید تا نتایج را بررسی کنید. 