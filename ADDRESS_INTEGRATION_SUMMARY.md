# خلاصه یکپارچه‌سازی آدرس

## مشکل اصلی
در فایل `main.js` در خط 108، یک کامنت وجود داشت که می‌گفت:
```javascript
if (user.action == "add-address") {
  // HERE YOUR AJAX FUNCTION TO SAVE NEW ADDRESS
  // example: mohsenFunc({ success: successAACB, error: errorAACB });
}
```

## راه‌حل پیاده‌سازی شده

### 1. استفاده از توابع موجود
تابع‌های مورد نیاز قبلاً در `custom.js` پیاده‌سازی شده بودند:
- `vosSaveUserAddress()` - برای ذخیره آدرس
- `vosFetchUserAddresses()` - برای دریافت لیست آدرس‌ها
- `handleAddressSave()` - برای جمع‌آوری داده‌ها و ذخیره
- `window.vosHandleAddressNext()` - برای مدیریت مرحله بعد

### 2. تغییرات در `main.js`

#### الف) اضافه کردن فراخوانی تابع
```javascript
if (user.action == "add-address") {
  // Use the existing vosHandleAddressNext function
  if (typeof window.vosHandleAddressNext === 'function') {
    window.vosHandleAddressNext({
      currentStep,
      currentStepIndex,
      direction,
      toStep,
      setAddressFields,
      main,
      disableButtonCan
    });
    return;
  }
  
  // Fallback to direct save if function not available
  handleAddressSave()
    .then(response => {
      // After successful save, fetch updated address list
      vosFetchUserAddresses({
        success: (list) => {
          successAACB(list);
        },
        error: () => {
          errorAACB();
        }
      });
    })
    .catch(error => {
      console.error('Error saving address:', error);
      errorAACB();
    });
}
```

#### ب) در دسترس قرار دادن callbacks
```javascript
// Make callbacks globally available
window.successAACB = successAACB;
window.errorAACB = errorAACB;
```

### 3. تغییرات در `custom.js`

#### الف) بهبود تابع `window.vosHandleAddressNext`
```javascript
window.vosHandleAddressNext = function(ctx) {
    const { currentStepIndex, direction, toStep, setAddressFields, main, disableButtonCan } = ctx;

    const nextBtn = document.getElementById('next-step-btn');
    if (nextBtn) nextBtn.disabled = true;

    handleAddressSave()
        .then(() => {
            // After successful save, fetch updated address list
            vosFetchUserAddresses({
                success: (list) => {
                    // بستن حالت fields
                    setAddressFields?.({ action: "" });
                    if (main && main.dataset) main.dataset.stepState = 'def';
                    disableButtonCan?.();
                    
                    // Call successAACB with the updated list
                    if (typeof successAACB === 'function') {
                        successAACB(list);
                    }
                },
                error: () => {
                    // در صورت خطا، در همین استپ بمان
                    if (typeof errorAACB === 'function') {
                        errorAACB();
                    }
                }
            });
        })
        .catch(() => {
            // در صورت خطا، در همین استپ بمان
            if (typeof errorAACB === 'function') {
                errorAACB();
            }
        })
        .finally(() => {
            if (nextBtn) nextBtn.disabled = false;
        });
};
```

#### ب) در دسترس قرار دادن توابع
```javascript
// Make functions globally available
window.vosSaveUserAddress = vosSaveUserAddress;
window.vosGetUserData = vosGetUserData;
window.vosClearUserData = vosClearUserData;
window.vosFetchUserAddresses = vosFetchUserAddresses;
window.handleAddressSave = handleAddressSave;
window.handleMobileLogin = handleMobileLogin;
window.persianToEnglishDigits = persianToEnglishDigits;
window.normalizeMobile = normalizeMobile;
```

## جریان کار

1. **کاربر روی "افزودن آدرس جدید" کلیک می‌کند**
   - تابع `showAddressFields({ action: "add-address" })` فراخوانی می‌شود
   - فرم آدرس نمایش داده می‌شود

2. **کاربر اطلاعات آدرس را وارد می‌کند**
   - فیلدهای `address-name`, `address-province`, `address-city`, `address-dl`, `vos-lat`, `vos-lng` پر می‌شوند

3. **کاربر روی "ادامه" کلیک می‌کند**
   - در `main.js` شرط `user.action == "add-address"` بررسی می‌شود
   - تابع `window.vosHandleAddressNext()` فراخوانی می‌شود

4. **ذخیره آدرس**
   - تابع `handleAddressSave()` داده‌ها را جمع‌آوری می‌کند
   - تابع `vosSaveUserAddress()` آدرس را در دیتابیس ذخیره می‌کند

5. **دریافت لیست به‌روزرسانی شده**
   - تابع `vosFetchUserAddresses()` لیست جدید آدرس‌ها را دریافت می‌کند
   - تابع `successAACB()` فراخوانی می‌شود و UI به‌روزرسانی می‌شود

## فیلدهای مورد نیاز

- `address-name` - نام آدرس (مثل: خانه، محل کار)
- `address-province` - استان (پیش‌فرض: تهران)
- `address-city` - شهر (پیش‌فرض: تهران)
- `address-dl` - جزئیات آدرس (اجباری)
- `vos-lat` - عرض جغرافیایی
- `vos-lng` - طول جغرافیایی

## PHP Handlers موجود

- `vos_save_address` - برای ذخیره آدرس جدید
- `vos_get_addresses` - برای دریافت لیست آدرس‌های کاربر

## تست

فایل `address-integration-test.html` برای تست عملکرد یکپارچه‌سازی ایجاد شده است.

## نکات مهم

1. تمام توابع در scope سراسری در دسترس هستند
2. خطاها به درستی مدیریت می‌شوند
3. UI بعد از ذخیره آدرس به‌روزرسانی می‌شود
4. دکمه "ادامه" در حین عملیات غیرفعال می‌شود 