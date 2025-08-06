// Digits Integration for Multi-Step Form
// This file handles Digits login/signup without page refresh

(function($) {
    'use strict';

    // Store form data to preserve it during login
    window.vosFormData = {
        currentStep: null,
        formData: {},
        isLoggedIn: false
    };

    // Initialize Digits integration
    function initDigitsIntegration() {
        console.log('Initializing Digits integration...');

        // Check if user is already logged in
        if (window.vosUserData && window.vosUserData.user_id) {
            window.vosFormData.isLoggedIn = true;
            enableNextButtonAfterLogin();
            return;
        }

        // Load Digits shortcode via AJAX
        loadDigitsShortcode();
    }

    // Load Digits shortcode via AJAX
    function loadDigitsShortcode() {
        const loadingEl = document.getElementById('digits-loading');
        const contentEl = document.getElementById('digits-content');

        if (!loadingEl || !contentEl) {
            console.warn('digits-loading یا digits-content در صفحه نیست.');
            return;
        }

        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';

        // گرفتن form_id و instance_id از inputهای مخفی
        var formId     = document.querySelector('input[name="digits_form"]')?.value || '';
        var instanceId = document.querySelector('input[name="instance_id"]')?.value || '';

        $.post(ajaxurl, {
            action: 'load_digits_shortcode',
            _ajax_nonce: window.VOS?.nonces?.phone || '',
            digits_form: formId,
            instance_id: instanceId
        })
            .done(function(res) {
                if (res.success) {
                    contentEl.innerHTML = res.data.html;
                    loadingEl.style.display = 'none';
                    contentEl.style.display = 'block';
                    setupDigitsFormHandlers();  // بعد از تزریق HTML
                } else {
                    contentEl.innerHTML = res.data.fallback_html || "خطا در بارگذاری فرم";
                    loadingEl.style.display = 'none';
                    contentEl.style.display = 'block';
                    console.error('Digits load failed', res);
                }
            })
            .fail(function(err) {
                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
                contentEl.innerHTML = 'خطای ارتباط با سرور';
                console.error('AJAX error loading Digits:', err);
            });
    }

// فقط رویدادها؛ بدون هیچ منطق OTP/رجیستر دستی
    function setupDigitsFormHandlers() {
        // Digits خودش فرم و OTP و ثبت‌نام رو مدیریت می‌کنه
        $(document).off('digits:login:success digits:signup:success digits:error');

        $(document).on('digits:login:success digits:signup:success', function(e, data) {
            console.log('Digits auth success:', data);
            handleDigitsSuccess({
                user_id: data.user_id,
                phone: data.phone || data.mobile,
                logged_in: true
            });
        });

        $(document).on('digits:error', function(e, error) {
            console.error('Digits error:', error);
            showMessage(
                (error && error.message) || 'خطا در ورود/ثبت‌نام با Digits',
                'error'
            );
        });
    }


    // Wait for Digits form to be available in DOM
    function waitForDigitsForm() {
        const checkInterval = setInterval(function() {
            const digitsForm = document.querySelector('#digits-login-container .digits-form');
            const fallbackForm = document.querySelector('#digits-login-container .digits-fallback-form');

            if (digitsForm) {
                clearInterval(checkInterval);
                setupDigitsFormHandlers();
            } else if (fallbackForm) {
                clearInterval(checkInterval);
                setupFallbackFormHandlers();
            }
        }, 100);

        // Timeout after 10 seconds
        setTimeout(function() {
            clearInterval(checkInterval);
            console.error('No login form found after 10 seconds');
        }, 10000);
    }

    // Setup event handlers for Digits form
    function setupDigitsFormHandlers() {
        console.log('Setting up Digits form handlers...');

        // Listen for Digits events
        $(document).on('digits:login:success', handleDigitsLoginSuccess);
        $(document).on('digits:signup:success', handleDigitsSignupSuccess);
        $(document).on('digits:error', handleDigitsErrorEvent);

        // Override Digits form submission to prevent page refresh
        const digitsForm = document.querySelector('#digits-login-container .digits-form');
        if (digitsForm) {
            $(digitsForm).on('submit', function(e) {
                e.preventDefault();
                console.log('Digits form submission intercepted');
                return false;
            });
        }

        // Monitor for login success
        monitorDigitsLogin();
    }

    // Setup event handlers for fallback form
// داخل setupFallbackFormHandlers()
    function setupFallbackFormHandlers() {
        // وقتی Login اولیه
        $('#mobile-login-btn').on('click', function(e) {
            e.preventDefault();
            var mobile = $('input[name="mobile"]').val().replace(/\D/g,'');
            $.post(ajaxurl, { action: 'vos_send_login_otp', mobile: mobile })
                .done(function(res) {
                    showMessage(res.data.message,'success');
                    showLoginOtpForm(mobile);
                })
                .fail(function(xhr) {
                    showMessage(xhr.responseJSON.data.message,'error');
                });
        });
    }

// نمایش فرم ورود OTP
    function showLoginOtpForm(mobile) {
        $('#digits-content').html(
            '<div class="otp-form">' +
            '<p>کد ارسال شده را وارد کنید:</p>' +
            '<input type="tel" id="login-otp" maxlength="6" />' +
            '<button id="login-otp-verify">تأیید</button>' +
            '</div>'
        );
        $('#login-otp-verify').on('click', function() {
            var otp = $('#login-otp').val();
            $.post(ajaxurl, { action:'vos_verify_login_otp', mobile: mobile, otp: otp })
                .done(function(res) {
                    handleDigitsSuccess({ user_id:res.data.user_id, phone:mobile, logged_in:true });
                })
                .fail(function(xhr) {
                    showMessage(xhr.responseJSON.data.message,'error');
                });
        });
    }

// برای ثبت‌نام، ابتدا فرم اولیه با موبایل+ایمیل+نام
    function showRegistrationForm() {
        $('#digits-content').html(
            '<div class="reg-form">' +
            '<input type="text" id="reg-name" placeholder="نام و نام خانوادگی" />' +
            '<input type="tel" id="reg-mobile" placeholder="موبایل" maxlength="11" />' +
            '<input type="email" id="reg-email" placeholder="ایمیل" />' +
            '<button id="reg-send-otp">ارسال کد</button>' +
            '</div>'
        );
        $('#reg-send-otp').on('click', function() {
            var name = $('#reg-name').val().trim();
            var mobile = $('#reg-mobile').val().replace(/\D/g,'');
            var email = $('#reg-email').val().trim();
            $.post(ajaxurl, { action:'vos_send_register_otp', name:name, mobile:mobile, email:email })
                .done(function(res) {
                    showMessage(res.data.message,'success');
                    showRegisterOtpForm(name, mobile, email);
                })
                .fail(function(xhr) {
                    showMessage(xhr.responseJSON.data.message,'error');
                });
        });
    }

    function showRegisterOtpForm(name, mobile, email) {
        $('#digits-content').html(
            '<div class="otp-form">' +
            `<p>کد ارسال شده به ${mobile} را وارد کنید:</p>` +
            '<input type="tel" id="reg-otp" maxlength="6" />' +
            '<button id="reg-otp-verify">تأیید ثبت‌نام</button>' +
            '</div>'
        );
        $('#reg-otp-verify').on('click', function() {
            var otp = $('#reg-otp').val();
            $.post(ajaxurl, {
                action:'vos_verify_register_otp',
                name: name, mobile: mobile, email: email, otp: otp
            })
                .done(function(res) {
                    showMessage('ثبت‌نام موفقیت‌آمیز بود!','success');
                    handleDigitsSuccess({ user_id:res.data.user_id, phone:mobile, logged_in:true });
                })
                .fail(function(xhr) {
                    showMessage(xhr.responseJSON.data.message,'error');
                });
        });
    }


    // Handle fallback login
    function handleFallbackLogin() {
        var mobile = $('input[name="mobile"]').val().replace(/\D+/g, '').substring(0,11);
        if (!mobile) {
            showMessage('لطفاً شماره موبایل را وارد کنید.', 'error');
            return;
        }
        showMessage('در حال بررسی…', 'info');

        $.post(ajaxurl, { action: 'vos_verify_mobile', mobile: mobile })
            .done(function(response) {
                if (response.success && response.data.valid) {
                    // ورود موفق
                    handleDigitsSuccess({ user_id: response.data.user_id, phone: response.data.mobile, logged_in: true });
                } else {
                    // شماره ثبت نشده: نمایش فرم ثبت‌نام
                    showRegistrationForm(mobile);
                }
            })
            .fail(function() {
                showMessage('خطا در ارتباط با سرور.', 'error');
            });
    }


    function loadDMPageShortcode() {
        const loadingEl = document.getElementById('dm-loading');
        const contentEl = document.getElementById('dm-content');
        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';

        jQuery.post(ajaxurl, {
            action: 'load_dm_page_shortcode',
            _ajax_nonce: window.VOS?.nonces?.dm || ''
        })
            .done(function(res) {
                if (res.success) {
                    contentEl.innerHTML = res.data.html;
                    loadingEl.style.display = 'none';
                    contentEl.style.display = 'block';
                } else {
                    contentEl.innerHTML = res.data.fallback_html || "خطا در بارگذاری فرم";
                    loadingEl.style.display = 'none';
                    contentEl.style.display = 'block';
                    console.error('DM load failed', res);
                }
            })
            .fail(function(err) {
                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
                contentEl.innerHTML = 'خطای ارتباط با سرور';
                console.error('AJAX error loading DM:', err);
            });
    }


    function handleFallbackRegister() {
        var name   = $('#reg-name').val().trim();
        var mobile = $('#reg-mobile').val().trim();
        if (!name) {
            showMessage('لطفاً نام خود را وارد کنید.', 'error');
            return;
        }
        showMessage('در حال ثبت‌نام…', 'info');

        $.post(ajaxurl, {
            action: 'vos_register_mobile',
            name: name,
            mobile: mobile
        })
            .done(function(response) {
                if (response.success && response.data.user_id) {
                    showMessage('ثبت‌نام با موفقیت انجام شد!', 'success');
                    // بعد از ثبت‌نام، می‌توانید مستقیم وارد کنید:
                    handleDigitsSuccess({ user_id: response.data.user_id, phone: mobile, logged_in: true });
                } else {
                    showMessage(response.data.message || 'خطا در ثبت‌نام', 'error');
                }
            })
            .fail(function() {
                showMessage('خطا در ارتباط با سرور.', 'error');
            });
    }




    // Monitor Digits login process
    function monitorDigitsLogin() {
        // Check periodically if user is logged in
        const checkInterval = setInterval(function() {
            if (window.vosFormData && window.vosFormData.isLoggedIn) {
                clearInterval(checkInterval);
                return;
            }

            // Check if user is logged in by looking for success indicators
            const successMessage = document.querySelector('#digits-login-container .digits-success');
            const errorMessage = document.querySelector('#digits-login-container .digits-error');

            if (successMessage) {
                console.log('Digits login success detected');
                handleDigitsLoginSuccess(null, { success: true });
                clearInterval(checkInterval);
            } else if (errorMessage) {
                console.log('Digits login error detected');
                handleDigitsError(null, { error: errorMessage.textContent });
            }
        }, 1000);

        // Stop monitoring after 5 minutes
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 300000);
    }





    // Validate mobile number
    function isValidMobile(mobile) {
        const cleanMobile = mobile.replace(/\D/g, '');
        return cleanMobile.length === 11 && cleanMobile.startsWith('09');
    }

    // Handle successful Digits login/signup
    function handleDigitsSuccess(data) {
        console.log('Digits login/signup successful:', data);

        // Store user data
        if (data.user_id) {
            if (!window.vosUserData) {
                window.vosUserData = {};
            }
            window.vosUserData.user_id = data.user_id;
            window.vosUserData.phone = data.phone || data.mobile;
            window.vosFormData.isLoggedIn = true;
            
            console.log('User data stored:', window.vosUserData);
        }

        showMessage('ورود موفقیت‌آمیز بود!', 'success');

        // حفظ مرحله قبل از رفرش احتمالی (فقط برای لاگین دیجیتس)
        const currentStep = document.querySelector('.form--step.current');
        if (currentStep && currentStep.dataset.step === '20' && typeof window.preserveStepBeforeReload === 'function') {
            window.preserveStepBeforeReload();
        }

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

    // Handle Digits errors
    function handleDigitsError(error) {
        console.error('Digits error:', error);

        let message = 'خطا در ورود';
        if (typeof error === 'string') {
            message = error;
        } else if (error && error.message) {
            message = error.message;
        }

        showMessage(message, 'error');
    }

    // Handle Digits login success event
    function handleDigitsLoginSuccess(event, data) {
        console.log('Digits login success event:', data);

        // Get user data from Digits
        const userData = {
            user_id: data.user_id || null,
            phone: data.phone || data.mobile || null,
            logged_in: true
        };

        handleDigitsSuccess(userData);
    }

    // Handle Digits signup success event
    function handleDigitsSignupSuccess(event, data) {
        console.log('Digits signup success event:', data);

        // Get user data from Digits
        const userData = {
            user_id: data.user_id || null,
            phone: data.phone || data.mobile || null,
            logged_in: true
        };

        handleDigitsSuccess(userData);
    }

    // Handle Digits error event
    function handleDigitsErrorEvent(event, error) {
        console.log('Digits error event:', error);

        let message = 'خطا در ورود';
        if (typeof error === 'string') {
            message = error;
        } else if (error && error.message) {
            message = error.message;
        } else if (error && error.error) {
            message = error.error;
        }

        showMessage(message, 'error');
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

    // Enable next button after successful login
    function enableNextButtonAfterLogin() {
        const nextBtn = document.getElementById('next-step-btn');
        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
        }

        // Update form state
        window.vosFormData.isLoggedIn = true;

        // Hide login container if it's visible
        const dmContent = document.getElementById('dm-content');
        if (dmContent) {
            dmContent.style.display = 'none';
        }

        // حذف مرحله لاگین (step-20) از DOM
        const loginStep = document.querySelector('.step-20');
        if (loginStep) {
            loginStep.parentNode.removeChild(loginStep);
        }

        // Update step numbers for logged in users
        updateStepNumbersForLoggedInUser();

        // Refresh steps list used in main.js if available
        if (typeof window.refreshFormSteps === 'function') {
            window.refreshFormSteps();
        }

        // اگر تابع setStepAfterLogin موجود است، آن را فراخوانی کن
        if (typeof window.setStepAfterLogin === 'function') {
            setTimeout(function() {
                const success = window.setStepAfterLogin();
                if (success) {
                    console.log('Successfully set step after login');
                } else {
                    console.log('Failed to set step after login, trying navigateAfterLogin');
                    if (typeof window.navigateAfterLogin === 'function') {
                        window.navigateAfterLogin();
                    }
                }
            }, 500);
        } else if (typeof window.navigateAfterLogin === 'function') {
            setTimeout(function() {
                window.navigateAfterLogin();
            }, 500);
        } else {
            // اگر هیچ تابعی موجود نیست، مستقیماً به مرحله 6 برو
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
                    
                    // جلوگیری از reset شدن مرحله در checkAndFixStepState
                    if (window.lastLoginStep !== undefined) {
                        window.lastLoginStep = '6';
                    }
                    
                    console.log('Directly navigated to step 6');
                }
            }, 500);
        }

        console.log('Next button enabled after login');
    }

    // Update step numbers for logged in user
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

    // Save current form state
    function saveFormState() {
        const currentStep = document.querySelector('.form--step.current');
        if (currentStep) {
            window.vosFormData.currentStep = currentStep.dataset.step;
        }

        // Save form data
        const form = document.querySelector('form');
        if (form) {
            const formData = new FormData(form);
            window.vosFormData.formData = Object.fromEntries(formData);
        }
    }

    // Restore form state
    function restoreFormState() {
        if (window.vosFormData.currentStep) {
            // Navigate to the saved step
            const stepElement = document.querySelector(`[data-step="${window.vosFormData.currentStep}"]`);
            if (stepElement) {
                // Trigger navigation to the saved step
                if (typeof window.toStep === 'function') {
                    const currentStep = document.querySelector('.form--step.current');
                    const currentIndex = currentStep ? parseInt(currentStep.dataset.step) : 0;
                    const targetIndex = parseInt(window.vosFormData.currentStep);
                    window.toStep(currentIndex, targetIndex, 'next');
                }
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Digits integration script loaded');
        console.log('VOS object:', window.VOS);
        console.log('Nonce available:', window.VOS?.nonces?.phone);

        // Save form state before any navigation
        $(document).on('click', '#next-step-btn, #prev-step-btn', function() {
            saveFormState();
        });

        // Initialize Digits integration
        initDigitsIntegration();
    });

    // Make functions globally available
    window.vosDigitsIntegration = {
        init: initDigitsIntegration,
        saveState: saveFormState,
        restoreState: restoreFormState,
        enableNextButton: enableNextButtonAfterLogin
    };

})(jQuery);

// تابع لود AJAX شورتکد [dm-page]



// هندل کلیک روی دکمه ورود/ثبت‌نام (جلوگیری از تغییر آدرس و لود فرم)
jQuery(document).on('click', '#start-login-btn', function(e){
    e.preventDefault();
    loadDMPage(); // فرم AJAXی را لود کن
});


// فراخوانی تابع لود فرم در شروع
jQuery(document).ready(function() {
    loadDMPage();
});

// هندل کلیک روی دکمه ورود/ثبت‌نام
jQuery(document).on('click', '.btn-login', function(e){
    e.preventDefault(); // جلو تغییر آدرس را می‌گیرد
    // فقط مرحله ورود/ثبت‌نام را نمایش بده (همان فرم AJAXی را لود کن)
    jQuery('#login-step').show();
    jQuery('#otp-step, #profile-step').hide();
    // اگر فرم باید ری‌لود شود دوباره loadDMPage() را صدا کن
    // loadDMPage();
});
// jQuery(document).on('click', '#dm-content .digits-login-modal', function(e){
//     e.preventDefault();
//     show_digits_login_modal(jQuery(this));
//     return false;
// });
setTimeout(function(){
    var btn = jQuery('#dm-content .digits-login-modal');
    if (btn.length && typeof show_digits_login_modal === "function") {
        btn.trigger('click');
    }
}, 300);

