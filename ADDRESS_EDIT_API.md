# API ویرایش آدرس برای فرانت‌اند کار

## توابع در دسترس

### 1. `window.handleAddressEdit(addressData)`
این تابع به صورت خودکار وقتی روی دکمه ویرایش آدرس کلیک می‌شود فراخوانی می‌شود.

**پارامترها:**
```javascript
addressData = {
    id: number,              // شناسه آدرس
    address_name: string,    // نام آدرس (مثل: خانه، محل کار)
    address_city: string,    // شهر
    address_province: string, // استان
    address_dl: string,      // جزئیات آدرس
    latitude: number,        // عرض جغرافیایی
    longitude: number        // طول جغرافیایی
}
```

**مثال استفاده:**
```javascript
// این تابع به صورت خودکار فراخوانی می‌شود
window.handleAddressEdit = function(addressData) {
    console.log('اطلاعات آدرس برای ویرایش:', addressData);
    
    // تنظیم مارکر روی نقشه
    setMapMarker(addressData.latitude, addressData.longitude, addressData.id);
    
    // نمایش فرم ویرایش
    showEditForm(addressData);
};
```

### 2. `window.getSelectedAddressData()`
این تابع اطلاعات آدرس انتخاب شده را برمی‌گرداند.

**مثال استفاده:**
```javascript
const selectedAddress = window.getSelectedAddressData();
if (selectedAddress) {
    console.log('آدرس انتخاب شده:', selectedAddress);
    // تنظیم مارکر برای آدرس انتخاب شده
    setMapMarker(selectedAddress.latitude, selectedAddress.longitude, selectedAddress.id);
}
```

### 3. `window.setMapMarker(latitude, longitude, addressId)`
تابع کمکی برای تنظیم مارکر روی نقشه.

**پارامترها:**
- `latitude`: عرض جغرافیایی
- `longitude`: طول جغرافیایی  
- `addressId`: شناسه آدرس

**مثال استفاده:**
```javascript
window.setMapMarker = function(latitude, longitude, addressId) {
    if (window.map && latitude && longitude) {
        // حذف مارکر قبلی
        if (window.currentMarker) {
            window.map.removeLayer(window.currentMarker);
        }
        
        // اضافه کردن مارکر جدید
        window.currentMarker = L.marker([latitude, longitude]).addTo(window.map);
        window.map.setView([latitude, longitude], 15);
        
        // اضافه کردن پاپ‌آپ
        window.currentMarker.bindPopup(`آدرس: ${addressId}`).openPopup();
    }
};
```

## جریان کار

1. **کاربر روی دکمه ویرایش کلیک می‌کند**
   - تابع `editAddress()` فراخوانی می‌شود
   - اطلاعات آدرس از `address_meta_data` خوانده می‌شود
   - تابع `window.handleAddressEdit()` فراخوانی می‌شود

2. **فرانت‌اند کار در `handleAddressEdit` کد خود را می‌نویسد**
   - تنظیم مارکر روی نقشه
   - نمایش فرم ویرایش
   - هر کار دیگری که نیاز دارد

3. **اطلاعات آدرس در دسترس است**
   - `addressData.id`: شناسه آدرس
   - `addressData.latitude`: عرض جغرافیایی
   - `addressData.longitude`: طول جغرافیایی
   - سایر اطلاعات آدرس

## نکات مهم

- تمام توابع در scope سراسری (`window`) در دسترس هستند
- اطلاعات آدرس در `address_meta_data` به صورت JSON ذخیره شده است
- طول و عرض جغرافیایی به صورت عددی هستند
- در صورت عدم وجود طول و عرض، مقدار `null` یا `undefined` خواهد بود

## مثال کامل

```javascript
// تنظیم تابع handleAddressEdit
window.handleAddressEdit = function(addressData) {
    console.log('ویرایش آدرس:', addressData);
    
    // تنظیم مارکر روی نقشه
    if (addressData.latitude && addressData.longitude) {
        setMapMarker(addressData.latitude, addressData.longitude, addressData.id);
    }
    
    // نمایش فرم ویرایش
    showEditForm(addressData);
};

// تابع تنظیم مارکر
window.setMapMarker = function(latitude, longitude, addressId) {
    if (window.map) {
        // کد مربوط به نقشه
        console.log(`مارکر برای آدرس ${addressId} در مختصات ${latitude}, ${longitude} تنظیم شد`);
    }
};
``` 