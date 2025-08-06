# API تنظیم مارکر روی نقشه

## تابع اصلی: `window.receiveAddressFromColleague(addressInfo)`

این تابع اطلاعات آدرس را از همکار شما دریافت می‌کند و مارکر را روی نقشه تنظیم می‌کند.

### نحوه استفاده:

```javascript
// همکار شما این تابع را فراخوانی می‌کند
window.receiveAddressFromColleague({
    id: 123,
    latitude: 35.6892,
    longitude: 51.3890,
    address_name: "خانه",
    address_dl: "تهران، خیابان ولیعصر"
});
```

### پارامترهای ورودی:

```javascript
addressInfo = {
    id: number,           // شناسه آدرس (ضروری)
    latitude: number,     // عرض جغرافیایی (ضروری)
    longitude: number,    // طول جغرافیایی (ضروری)
    address_name: string, // نام آدرس (اختیاری)
    address_dl: string    // جزئیات آدرس (اختیاری)
}
```

### خروجی:
- `true`: در صورت موفقیت
- `false`: در صورت خطا یا اطلاعات ناقص

## تابع کمکی: `window.getCurrentAddressInfo()`

این تابع اطلاعات آدرس انتخاب شده را برمی‌گرداند (برای استفاده همکار).

### مثال استفاده:

```javascript
// همکار شما می‌تواند از این تابع استفاده کند
const addressInfo = window.getCurrentAddressInfo();
if (addressInfo) {
    console.log('آدرس انتخاب شده:', addressInfo);
    // حالا می‌تواند این اطلاعات را به تابع شما بدهد
    window.receiveAddressFromColleague(addressInfo);
}
```

## مثال کامل:

```javascript
// وقتی کاربر روی دکمه ویرایش کلیک می‌کند
window.handleAddressEdit = function(addressData) {
    // همکار شما این اطلاعات را به تابع شما می‌دهد
    window.receiveAddressFromColleague({
        id: addressData.id,
        latitude: addressData.latitude,
        longitude: addressData.longitude,
        address_name: addressData.address_name,
        address_dl: addressData.address_dl
    });
};
```

## نکات مهم:

1. **اطلاعات ضروری**: `id`, `latitude`, `longitude` باید حتماً وجود داشته باشند
2. **نقشه**: تابع `window.map` باید از قبل تعریف شده باشد
3. **Leaflet**: این کد برای کتابخانه Leaflet نوشته شده است
4. **مارکر قبلی**: مارکر قبلی به صورت خودکار حذف می‌شود

## خطاهای احتمالی:

- اگر `window.map` تعریف نشده باشد، پیام هشدار در کنسول نمایش داده می‌شود
- اگر اطلاعات ناقص باشد، تابع `false` برمی‌گرداند
- تمام خطاها در کنسول مرورگر ثبت می‌شوند 