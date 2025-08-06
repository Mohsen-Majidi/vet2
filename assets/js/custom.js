// Global variables to store user and address data
window.vosUserData = {
    user_id: null,
    address_name: null,
    address_id: null
};

// Mobile number utilities
function persianToEnglishDigits(str) {
    const persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    const englishDigits = ['0','1','2','3','4','5','6','7','8','9'];
    let res = str;
    for (let i = 0; i < 10; i++) {
        res = res.replace(new RegExp(persianDigits[i], 'g'), englishDigits[i]);
    }
    return res;
}

function normalizeMobile(mobile) {
    let m = persianToEnglishDigits(mobile).replace(/\s+/g, '');
    if (m.startsWith('+98')) {
        // do nothing
    } else if (m.startsWith('09')) {
        m = '+98' + m.substring(1);
    } else if (m.startsWith('9')) {
        m = '+98' + m;
    }
    return m;
}

// Function to save user address
// ذخیره آدرس کاربر (هماهنگ با PHP: action=vos_save_address)
function vosSaveUserAddress(addressData) {
    console.log('addressData =', addressData);
    return new Promise((resolve, reject) => {
        const ajaxurl = (window.VOS && VOS.ajaxurl) ? VOS.ajaxurl : (window.ajaxurl || '/wp-admin/admin-ajax.php');
        const nonce   = (window.VOS && VOS.nonce) ? VOS.nonce : '';

        const payload = new URLSearchParams();
        payload.append('action', 'vos_save_address');
        if (nonce) payload.append('_ajax_nonce', nonce);

        // نام‌های فیلد مطابق PHP:
        payload.append('address_name',     addressData.address_name);
        payload.append('address_city',     addressData.address_city);
        payload.append('address_province', addressData.address_province);
        payload.append('address_dl',       addressData.address_dl);

        const latVal = addressData.latitude ?? addressData.lat;
        const lngVal = addressData.longitude ?? addressData.lng;

        if (latVal !== undefined && latVal !== '') payload.append('latitude',  latVal);
        if (lngVal !== undefined && lngVal !== '') payload.append('longitude', lngVal);

        fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: payload.toString(),
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(response => {
                console.log('AJAX Response:', response);
                if (response && response.success) {
                    // ذخیره‌ی داده‌های برگردانده‌شده (اختیاری)
                    window.vosUserData.user_id      = response.user_id || null;
                    window.vosUserData.address_name = addressData.address_name;
                    window.vosUserData.address_id   = response.id || response.address_id || null;
                    resolve(response);
                } else {
                    const msg = (response && response.data && response.data.message) || response?.message || 'خطا در ذخیره آدرس.';
                    reject(msg);
                }
            })
            .catch(err => {
                console.error('AJAX error:', err);
                reject('ارتباط با سرور برقرار نشد.');
            });
    });
}


// Function to get stored user data
function vosGetUserData() {
    return window.vosUserData;
}

// Function to clear stored user data
function vosClearUserData() {
    window.vosUserData = {
        user_id:      null,
        address_name: null,   // نام انتخاب‌شده
        address_id:   null,   // شناسهٔ انتخاب‌شده
        addresses:    {}      // ← آبجکت انجمنی { address_name : id }
    };
}

function vosFetchUserAddresses(cb = { success: () => {}, error: () => {} }) {
    fetch(`${VOS.ajaxurl}?action=vos_get_addresses`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(json => {
            if (!json.success) throw new Error(json.data?.message || 'fetch error');

            const list = json.data.addresses || [];
            const dict = Object.fromEntries(list.map(r => [r.address_name, r.id]));

            window.vosUserData.addresses = dict;


            cb.success(list, dict);
        })
        .catch(err => {
            console.error(err);
            cb.error(err);
        });
}


// Example usage function for saving address
function handleAddressSave() {
    console.log('handleAddressSave called');

    const data = {
        address_name:      document.getElementById('address-name')?.value?.trim() || 'خانه',
        address_city:      document.getElementById('address-city')?.value?.trim() || 'تهران',
        address_province:  document.getElementById('address-province')?.value?.trim() || 'تهران',
        address_dl:        document.getElementById('address-dl')?.value?.trim() || '',
        latitude:               document.getElementById('vos-lat')?.value?.trim() || '',
        longitude:               document.getElementById('vos-lng')?.value?.trim() || ''
    };

    // Show loading
    const main = document.querySelector('main.container');
    if (main) {
        main.classList.add('loading');
    }

    return vosSaveUserAddress(data).then(res => {
        // نمایش در خلاصه استپ ۸
        const sd = document.querySelector('#sd-address .detail');
        if (sd) sd.textContent = data.address_dl || '—';
        
        // Hide loading after successful save
        if (main) {
            main.classList.remove('loading');
        }
        
        return res;
    }).catch(err => {
        // Hide loading on error
        if (main) {
            main.classList.remove('loading');
        }
        alert(err);
        throw err;
    });
}

async function fetchDevToken () {
    const r  = await fetch(
        '/wp-admin/admin-ajax.php?action=vos_get_token',
        { method:'POST', credentials:'same-origin' }
    );
    const { data } = await r.json();
    return data.token;
}

// Mobile login handler
async function handleMobileLogin () {
    const mobileInput = document.querySelector('#mobile-login-form input[name="mobile"]');
    const messageEl = document.getElementById('mobile-login-message');
    const mobileRaw = mobileInput?.value;
    const token = await fetchDevToken();
    // پاک کردن کلاس‌های قبلی
    mobileInput?.classList.remove('error', 'success');
    messageEl?.classList.remove('error', 'success', 'info');

    if (!mobileRaw) {
        messageEl.textContent = 'لطفاً شماره موبایل را وارد کنید.';
        messageEl.classList.add('error');
        mobileInput?.classList.add('error');
        return;
    }

    // تبدیل اعداد فارسی به انگلیسی
    const mobile = persianToEnglishDigits(mobileRaw);

    // حذف کاراکترهای غیر عددی
    const cleanMobile = mobile.replace(/\D+/g, '');

    // اعتبارسنجی طول و پیشوند
    if (cleanMobile.length !== 11) {
        messageEl.textContent = 'شماره باید دقیقاً ۱۱ رقم باشد.';
        messageEl.classList.add('error');
        mobileInput?.classList.add('error');
        return;
    }

    if (!cleanMobile.startsWith('09')) {
        messageEl.textContent = 'شماره باید با 09 شروع شود.';
        messageEl.classList.add('error');
        mobileInput?.classList.add('error');
        return;
    }

    messageEl.textContent = 'در حال بررسی ...';
    messageEl.classList.add('info');
    const loginBtn = document.getElementById('mobile-login-btn');
    if (loginBtn) loginBtn.disabled = true;

    jQuery.ajax({
        url: window.ajaxurl || '/wp-admin/admin-ajax.php',
        method: 'POST',
        data: {
            action: 'check_mobile_digits',
            mobile: cleanMobile,
            _token : token,
            _ajax_nonce: window.VOS?.nonces?.phone || ''
        },
        success: function(response) {
            if (response.success && response.data) {
                const data = response.data;

                if (data.ok) {
                    if (data.code === 'registered') {
                        messageEl.textContent = data.message;
                        messageEl.classList.add('success');
                        mobileInput?.classList.add('success');

                        // ذخیره اطلاعات کاربر
                        window.vosUserData.user_id = data.user_id;
                        window.vosUserData.phone = data.phone;

                        // فعال کردن دکمه ادامه
                        if (typeof window.enableNextButtonAfterMobileLogin === 'function') {
                            window.enableNextButtonAfterMobileLogin();
                        }
                    } else if (data.code === 'not_registered') {
                        messageEl.textContent = data.message;
                        messageEl.classList.add('error');
                        mobileInput?.classList.add('error');
                    }
                } else {
                    messageEl.textContent = data.message || 'خطا در اعتبارسنجی.';
                    messageEl.classList.add('error');
                    mobileInput?.classList.add('error');
                }
            } else {
                messageEl.textContent = 'خطایی رخ داده است.';
                messageEl.classList.add('error');
                mobileInput?.classList.add('error');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr);
            messageEl.textContent = 'ارتباط با سرور برقرار نشد.';
            messageEl.classList.add('error');
            mobileInput?.classList.add('error');
        },
        complete: function() {
            if (loginBtn) loginBtn.disabled = false;
        }
    });
}

// Initialize when document is ready
jQuery(document).ready(function($){
    console.log('Custom JS loaded successfully');

    // دکمه ثبت آدرس (اگر در مارکاپ دارید)
    $(document).on('click', '#add-new-address', function(e){
        e.preventDefault(); e.stopPropagation();
        handleAddressSave();
    });
});


// تابع فعال کردن دکمه ادامه بعد از لاگین موفق
window.enableNextButtonAfterMobileLogin = function() {
    const nextBtn = document.getElementById('next-step-btn');
    if (nextBtn) {
        nextBtn.disabled = false;
        nextBtn.style.opacity = '1';
        nextBtn.style.cursor = 'pointer';
    }
};

// Make functions globally available
window.vosSaveUserAddress = vosSaveUserAddress;
window.vosGetUserData = vosGetUserData;
window.vosClearUserData = vosClearUserData;
window.vosFetchUserAddresses = vosFetchUserAddresses;
window.handleAddressSave = handleAddressSave;
window.handleMobileLogin = handleMobileLogin;
window.persianToEnglishDigits = persianToEnglishDigits;
window.normalizeMobile = normalizeMobile;


// این تابع توسط main.js صدا زده می‌شود
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

// تابع کمکی برای فرانت‌اند کار - تنظیم مارکر روی نقشه
window.handleAddressEdit = function(addressData) {
    console.log('Address edit triggered:', addressData);
    
    // اینجا فرانت‌اند کار می‌تواند کد مربوط به تنظیم مارکر روی نقشه را بنویسد
    // مثال:
    // setMapMarker(addressData.latitude, addressData.longitude, addressData.id);
    
    // همچنین می‌تواند فرم ویرایش آدرس را نمایش دهد
    if (addressData) {
        // نمایش فرم ویرایش با اطلاعات موجود
        showAddressFields({ 
            action: "edit-address",
            name: addressData.address_name,
            dl: addressData.address_dl,
            addressId: addressData.id,
            latitude: addressData.latitude,
            longitude: addressData.longitude
        });
        
        // تنظیم مقادیر در فیلدهای مخفی
        const latField = document.getElementById('vos-lat');
        const lngField = document.getElementById('vos-lng');
        if (latField) latField.value = addressData.latitude || '';
        if (lngField) lngField.value = addressData.longitude || '';
    }
};

// تابع کمکی برای دریافت اطلاعات آدرس انتخاب شده
window.getSelectedAddressData = function() {
    const selectedAddress = document.querySelector('input[name="address"]:checked');
    if (selectedAddress) {
        const addressElement = selectedAddress.closest('.address-radio');
        if (addressElement) {
            const data = JSON.parse(addressElement.querySelector(".address_meta_data").textContent);
            return {
                id: data.id,
                address_name: data.address_name,
                address_city: data.address_city,
                address_province: data.address_province,
                address_dl: data.address_dl,
                latitude: data.latitude,
                longitude: data.longitude
            };
        }
    }
    return null;
};

// تابع کمکی برای تنظیم مارکر روی نقشه (نمونه)
window.setMapMarker = function(latitude, longitude, addressId) {
    console.log(`Setting map marker for address ${addressId} at coordinates: ${latitude}, ${longitude}`);
    
    // اینجا فرانت‌اند کار کد مربوط به نقشه را می‌نویسد
    // مثال:
    // if (window.map && latitude && longitude) {
    //     // حذف مارکر قبلی
    //     if (window.currentMarker) {
    //         window.map.removeLayer(window.currentMarker);
    //     }
    //     
    //     // اضافه کردن مارکر جدید
    //     window.currentMarker = L.marker([latitude, longitude]).addTo(window.map);
    //     window.map.setView([latitude, longitude], 15);
    // }
};

// تابع دریافت اطلاعات آدرس از همکار و تنظیم مارکر روی نقشه
window.receiveAddressFromColleague = function(addressInfo) {
    console.log('اطلاعات آدرس از همکار دریافت شد:', addressInfo);
    
    // بررسی وجود اطلاعات ضروری
    if (!addressInfo || !addressInfo.latitude || !addressInfo.longitude || !addressInfo.id) {
        console.error('اطلاعات ناقص است. نیاز به latitude, longitude و id داریم.');
        return false;
    }
    
    // تنظیم مارکر روی نقشه
    setMapMarkerOnMap(addressInfo.latitude, addressInfo.longitude, addressInfo.id);
    
    return true;
};

// تابع دریافت مختصات و شناسه آدرس از تابع iman همکار
window.iman = function(latitude, longitude, addressId) {
    console.log(`دریافت اطلاعات از تابع iman: عرض=${latitude}, طول=${longitude}, شناسه=${addressId}`);
    
    // بررسی وجود اطلاعات ضروری
    if (!latitude || !longitude || !addressId) {
        console.error('اطلاعات ناقص است. نیاز به latitude, longitude و addressId داریم.');
        return false;
    }
    
    // تنظیم مارکر روی نقشه
    setMapMarkerOnMap(latitude, longitude, addressId);
    
    return true;
};

// تابع تنظیم مارکر روی نقشه
function setMapMarkerOnMap(latitude, longitude, addressId) {
    console.log(`تنظیم مارکر برای آدرس ${addressId} در مختصات: ${latitude}, ${longitude}`);
    
    // اینجا کد مربوط به نقشه را بنویسید
    if (window.map) {
        // حذف مارکر قبلی (اگر وجود دارد)
        if (window.currentMapMarker) {
            window.map.removeLayer(window.currentMapMarker);
        }
        
        // ایجاد مارکر جدید
        window.currentMapMarker = L.marker([latitude, longitude]).addTo(window.map);
        
        // تنظیم مرکز نقشه روی مارکر
        window.map.setView([latitude, longitude], 15);
        
        // اضافه کردن پاپ‌آپ
        window.currentMapMarker.bindPopup(`آدرس ID: ${addressId}`).openPopup();
        
        console.log('مارکر با موفقیت روی نقشه تنظیم شد');
    } else {
        console.warn('نقشه در دسترس نیست. window.map تعریف نشده است.');
    }
}

// تابع کمکی برای دریافت اطلاعات آدرس انتخاب شده (برای استفاده همکار)
window.getCurrentAddressInfo = function() {
    const selectedAddress = document.querySelector('input[name="address"]:checked');
    if (selectedAddress) {
        const addressElement = selectedAddress.closest('.address-radio');
        if (addressElement) {
            const data = JSON.parse(addressElement.querySelector(".address_meta_data").textContent);
            return {
                id: data.id,
                latitude: data.latitude,
                longitude: data.longitude,
                address_name: data.address_name,
                address_dl: data.address_dl
            };
        }
    }
    return null;
};
