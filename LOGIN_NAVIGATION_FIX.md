# رفع مشکل ناوبری بعد از لاگین دیجیتس

## مشکل
بعد از لاگین موفق دیجیتس در مرحله 20، به جای رفتن به مرحله 6، کاربر به مرحله صفر برمی‌گشت.

## علت مشکل
در فایل `templates/multi-form.php` در خط 954، بعد از لاگین موفق، `location.reload()` اجرا می‌شد که باعث بارگذاری مجدد صفحه و بازگشت به مرحله صفر می‌شد.

## راه‌حل

### 1. اصلاح Event Listener در `templates/multi-form.php`

**قبل:**
```javascript
document.addEventListener('digits_login_success', () => {
    const box = document.getElementById('dm-content');
    if (box) box.remove();
    location.reload(); // این خط مشکل‌ساز بود
});
```

**بعد:**
```javascript
document.addEventListener('digits_login_success', () => {
    const box = document.getElementById('dm-content');
    if (box) box.remove();
    
    // به جای reload، از تابع navigateAfterLogin استفاده کنیم
    if (typeof window.navigateAfterLogin === 'function') {
        window.navigateAfterLogin();
    } else {
        // اگر تابع موجود نیست، به استپ 6 برو
        const step6Element = document.querySelector('.form--step[data-step="6"]');
        if (step6Element) {
            // حذف کلاس current از تمام مراحل
            document.querySelectorAll('.form--step').forEach(s => {
                s.classList.remove('current');
            });
            
            // تنظیم مرحله 6 به عنوان فعلی
            step6Element.classList.add('current');
            
            // به‌روزرسانی main dataset
            const main = document.querySelector('main.container');
            if (main) {
                main.dataset.step = '6';
                main.dataset.stepState = "def";
            }
        }
    }
});
```

### 2. اصلاح Event Listener های دیگر

**قبل:**
```javascript
['digits_user_logged_in', 'digitLoggedIn', 'digits_login_success']
    .forEach(evt => document.addEventListener(evt, () => activateStep(7)));
```

**بعد:**
```javascript
['digits_user_logged_in', 'digitLoggedIn', 'digits_login_success']
    .forEach(evt => document.addEventListener(evt, () => {
        // به جای رفتن به استپ 7، از تابع navigateAfterLogin استفاده کنیم
        if (typeof window.navigateAfterLogin === 'function') {
            window.navigateAfterLogin();
        } else {
            activateStep(6);
        }
    }));
```

### 3. اصلاح تابع `handleDigitsSuccess` در `assets/js/digits-integration.js`

اضافه کردن ارسال event مناسب:

```javascript
function handleDigitsSuccess(data) {
    console.log('Digits login/signup successful:', data);

    // Store user data
    if (data.user_id) {
        window.vosUserData.user_id = data.user_id;
        window.vosUserData.phone = data.phone || data.mobile;
        window.vosFormData.isLoggedIn = true;
    }

    showMessage('ورود موفقیت‌آمیز بود!', 'success');

    // ارسال event برای اطلاع سایر بخش‌ها
    const loginEvent = new CustomEvent('digits_login_success', {
        detail: data
    });
    document.dispatchEvent(loginEvent);

    // Enable next button after a short delay
    setTimeout(function() {
        enableNextButtonAfterLogin();
    }, 1000);
}
```

### 4. اصلاح تابع `enableNextButtonAfterLogin`

اضافه کردن فراخوانی تابع `navigateAfterLogin`:

```javascript
function enableNextButtonAfterLogin() {
    // ... کدهای قبلی ...

    // اگر تابع navigateAfterLogin موجود است، آن را فراخوانی کن
    if (typeof window.navigateAfterLogin === 'function') {
        setTimeout(function() {
            window.navigateAfterLogin();
        }, 500);
    } else {
        // اگر تابع موجود نیست، مستقیماً به مرحله 6 برو
        setTimeout(function() {
            const step6Element = document.querySelector('.form--step[data-step="6"]');
            if (step6Element) {
                // حذف کلاس current از تمام مراحل
                document.querySelectorAll('.form--step').forEach(s => {
                    s.classList.remove('current');
                });
                
                // تنظیم مرحله 6 به عنوان فعلی
                step6Element.classList.add('current');
                
                // به‌روزرسانی main dataset
                const main = document.querySelector('main.container');
                if (main) {
                    main.dataset.step = '6';
                    main.dataset.stepState = "def";
                }
                
                console.log('Directly navigated to step 6');
            }
        }, 500);
    }

    console.log('Next button enabled after login');
}
```

### 5. اصلاح تابع `updateStepNumbersForLoggedInUser`

بهبود منطق شماره‌گذاری مراحل:

```javascript
function updateStepNumbersForLoggedInUser() {
    const steps = document.querySelectorAll('.form--step');
    let currentStepElement = null;
    
    steps.forEach((step, index) => {
        const currentStep = parseInt(step.dataset.step);
        
        // اگر مرحله 20 (لاگین) را حذف کرده‌ایم، مراحل بعد از 6 را یک شماره کم کن
        if (currentStep > 6) {
            const newStep = currentStep - 1;
            step.dataset.step = newStep;
            step.className = step.className.replace(/step-\d+/, `step-${newStep}`);
        }
        
        // Remember which step was current
        if (step.classList.contains('current')) {
            currentStepElement = step;
        }
    });
    
    // If we had a current step, make sure it's still current after renumbering
    if (currentStepElement) {
        // Remove current class from all steps
        steps.forEach(step => step.classList.remove('current'));
        // Add current class to the step that was current
        currentStepElement.classList.add('current');
    }
    
    console.log('Step numbers updated for logged in user');
}
```

## فایل تست

فایل `test-login-navigation.html` برای تست عملکرد ناوبری ایجاد شده است.

### 6. اصلاح تابع `checkAndFixStepState` در `assets/js/main.js`

بهبود منطق تشخیص مرحله بعد از لاگین:

```javascript
const checkAndFixStepState = () => {
  const currentStep = document.querySelector('.form--step.current');
  const allSteps = document.querySelectorAll('.form--step');

  console.log('Checking step state:', {
    currentStep: currentStep ? currentStep.dataset.step : 'none',
    totalSteps: allSteps.length,
    steps: Array.from(allSteps).map(s => ({ step: s.dataset.step, current: s.classList.contains('current') })),
    lastLoginStep: lastLoginStep
  });

  // اگر مرحله فعلی وجود دارد، آن را حفظ کن
  if (currentStep) {
    // Update main dataset
    if (main) {
      main.dataset.step = currentStep.dataset.step;
      if (!main.dataset.stepState) {
        main.dataset.stepState = "def";
      }
    }
    return currentStep;
  }

  // اگر هیچ مرحله‌ای فعلی نباشد، بررسی کن که آیا بعد از لاگین هستیم
  if (allSteps.length > 0) {
    // اگر بعد از لاگین هستیم و مرحله 6 وجود دارد، آن را تنظیم کن
    if (window.vosUserData && window.vosUserData.user_id) {
      const step6Element = document.querySelector('.form--step[data-step="6"]');
      if (step6Element) {
        console.log('User is logged in, setting step 6 as current');
        step6Element.classList.add('current');
        
        // Update main dataset
        if (main) {
          main.dataset.step = '6';
          main.dataset.stepState = "def";
        }
        
        return step6Element;
      }
    }
    
    // در غیر این صورت، اولین مرحله را تنظیم کن
    console.log('No current step found, setting first step as current');
    allSteps[0].classList.add('current');
    
    // Update main dataset
    if (main) {
      main.dataset.step = allSteps[0].dataset.step;
      main.dataset.stepState = "def";
    }
    
    return allSteps[0];
  }

  return null;
};
```

### 7. اضافه کردن تابع `setStepAfterLogin`

تابع جدید برای تنظیم مرحله بعد از لاگین:

```javascript
// تابع کمکی برای تنظیم مرحله بعد از لاگین
window.setStepAfterLogin = function() {
  console.log('Setting step after login...');
  
  // اگر کاربر لاگین است و مرحله 6 وجود دارد، آن را تنظیم کن
  if (window.vosUserData && window.vosUserData.user_id) {
    const step6Element = document.querySelector('.form--step[data-step="6"]');
    if (step6Element) {
      console.log('User is logged in, setting step 6 as current');
      
      // حذف کلاس current از تمام مراحل
      document.querySelectorAll('.form--step').forEach(s => {
        s.classList.remove('current');
      });
      
      // تنظیم مرحله 6 به عنوان فعلی
      step6Element.classList.add('current');
      
      // به‌روزرسانی main dataset
      if (main) {
        main.dataset.step = '6';
        main.dataset.stepState = "def";
      }
      
      return true;
    }
  }
  
  return false;
};
```

### 8. اصلاح Event Listener های window load

بهبود منطق تشخیص مرحله در زمان بارگذاری صفحه:

```javascript
window.addEventListener('load', () => {
  console.log('Window loaded, checking step state...');
  
  // اگر لاگین در حال انجام است، مرحله را ریست نکن
  if (isLoginInProgress) {
    console.log('Login in progress, skipping step reset');
    return;
  }
  
  // اگر کاربر لاگین است و مرحله 6 وجود دارد، آن را تنظیم کن
  if (window.vosUserData && window.vosUserData.user_id) {
    const step6Element = document.querySelector('.form--step[data-step="6"]');
    if (step6Element && !document.querySelector('.form--step.current')) {
      console.log('User is logged in, setting step 6 as current');
      step6Element.classList.add('current');
      if (main) {
        main.dataset.step = '6';
        main.dataset.stepState = "def";
      }
      return;
    }
  }
  
  // فقط اگر هیچ مرحله‌ای فعلی نباشد، بررسی کن
  const currentStep = document.querySelector('.form--step.current');
  if (!currentStep) {
    console.log('No current step found, checking step state...');
    checkAndFixStepState();
  } else {
    console.log('Current step already exists:', currentStep.dataset.step);
  }
});
```

## فایل‌های تست

- `test-login-navigation.html` - تست اولیه ناوبری
- `test-step-preservation.html` - تست حفظ مرحله بعد از لاگین

## نتیجه

با این تغییرات، بعد از لاگین موفق دیجیتس در مرحله 20، کاربر به درستی به مرحله 6 هدایت می‌شود و مشکل بازگشت به مرحله صفر حل شده است. تابع `checkAndFixStepState` حالا می‌تواند تشخیص دهد که کاربر لاگین است و مرحله 6 را به عنوان مرحله فعلی تنظیم کند. 