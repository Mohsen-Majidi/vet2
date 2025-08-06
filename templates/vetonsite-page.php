<?php
/*
Template Name: VetOnSite Page
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<body>
<?php
get_header();
include VOS_PATH . 'templates/multi-form.php';
?>

<script>
    (function($){
        var map, marker;
        var $addrDl   = $('#address-dl');       // textarea جزئیات آدرس
        var $addrCity = $('#address-city');     // فیلد شهر
        var $latInp   = $('#vos-lat');          // hidden lat
        var $lngInp   = $('#vos-lng');          // hidden lng
        var $hint     = $('#vos-address-hint'); // پیام هشدار (خارج از تهران)

        var INITIAL_CENTER = [35.724, 51.39];   // حدود تهران
        var INITIAL_ZOOM   = 12;

        // فقط وقتی واقعاً visible شد init می‌کنیم
        function ensureMapReady(){
            var el = document.getElementById('vos-map');
            if (!el) return;

            // اگر Leaflet هنوز لود نشده
            if (typeof L === 'undefined') {
                console.error('Leaflet is not loaded yet');
                setTimeout(ensureMapReady, 120);
                return;
            }

            // اگر کانتینر هنوز نمایش/عرض/ارتفاع ندارد، کمی بعد دوباره چک کن
            if (el.clientWidth === 0 || el.clientHeight === 0 || el.offsetParent === null) {
                setTimeout(ensureMapReady, 120);
                return;
            }

            // init فقط یک‌بار
            if (!map) initMap();

            // کمی تاخیر برای پایان رندر/ترنزیشن، سپس رفرش ابعاد
            setTimeout(function(){
                if (map) {
                    map.invalidateSize(true);
                    // یک فوکوس ملایم به تهران
                    if (!marker) map.setView(INITIAL_CENTER, INITIAL_ZOOM);
                }
            }, 80);
        }

        function initMap(){
            if (map || !document.getElementById('vos-map')) return;

            map = L.map('vos-map', {
                center: INITIAL_CENTER,
                zoom: INITIAL_ZOOM,
                scrollWheelZoom: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            map.on('click', function(e){
                placeMarker(e.latlng.lat, e.latlng.lng, true);
            });
        }

        function placeMarker(lat, lng, doReverse){
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function(ev){
                    var p = ev.target.getLatLng();
                    placeMarker(p.lat, p.lng, true);
                });
            }
            $latInp.val(lat.toFixed(6));
            $lngInp.val(lng.toFixed(6));
            if (doReverse) reverseGeocode(lat, lng);
        }

        async function reverseGeocode(lat, lng){
            $addrDl.val('در حال دریافت آدرس...');
            $hint.text('');

            // اول تلاش با سرور (داخلی)؛ بعداً فالبک به Nominatim
            var ajaxurl = (window.VOS && VOS.ajaxurl)
                ? VOS.ajaxurl
                : (window.ajaxurl || '');

            jQuery(function($){
                $('#mobile-login-btn').on('click', function(e){
                    e.preventDefault();

                    var raw = $('#mobile-input').val();
                    var cleanMobile = normalizeMobile(persianToEnglishDigits(raw));

                    $(this).prop('disabled', true);

                    $.ajax({
                        url: ajaxurl,
                        dataType: 'json',// ← اینجا از متغیر محلی استفاده کن
                        method: 'POST',
                        data: {
                            action: 'vos_send_otp',    // ← دقیقاً همین نامِ اکشن در PHP
                            mobile: cleanMobile,
                            nonce:  VOS.nonces.phone   // ← نامِ فیلد باید "nonce" باشه
                        }
                    })
                        .done(function(response){
                            console.log('✅ vos_send_otp response:', response);
                            if (response.success) {
                                alert('پیامک OTP ارسال شد');
                            } else {
                                alert('خطا: ' + response.data.message);
                            }
                        })
                        .fail(function(jqxhr, textStatus, error){
                            console.error('❌ AJAX failed:', textStatus, error);
                            alert('خطا در ارسال درخواست');
                        })
                        .always(function(){
                            $('#mobile-login-btn').prop('disabled', false);
                        });
                });
            });

            try {
                if (ajaxurl) {
                    const r = await fetch(ajaxurl + '?action=vos_reverse&lat=' + lat + '&lng=' + lng, { credentials:'same-origin' });
                    const j = await r.json();
                    if (j && j.success) {
                        fillFromReverse(j.data);
                        return;
                    }
                }
            } catch(e) {
                // ادامه با فالبک
            }

            try {
                const url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
                    + '&lat=' + encodeURIComponent(lat)
                    + '&lon=' + encodeURIComponent(lng)
                    + '&accept-language=fa&addressdetails=1';
                const r2 = await fetch(url, { headers: { 'Accept': 'application/json' }});
                const j2 = await r2.json();
                fillFromReverse({ display_name: j2.display_name, address: j2.address });
            } catch (e) {
                $addrDl.val('خطا در دریافت آدرس');
            }
        }

        function fillFromReverse(data){
            const display = data.display_name || data.formatted_address || '';
            $addrDl
                .val(display)
                .trigger('input')   // ← مهم!
                .trigger('change');

            const a = data.address || {};
            const cityGuess = (a.city || a.town || a.county || a.state || 'تهران');
            $addrCity.val(cityGuess);

            const inTehran = /تهران/.test(cityGuess) || /تهران/.test(a.state || '');
            if (!inTehran) $hint.text('⚠️ این نقطه خارج از محدوده تهران است.');
        }

        // دکمه «موقعیت من»
        $('#find-my-loc-btn').on('click', function(){
            if (!navigator.geolocation) {
                alert('مرورگر از موقعیت‌یابی پشتیبانی نمی‌کند.');
                return;
            }
            navigator.geolocation.getCurrentPosition(function(pos){
                var lat = pos.coords.latitude, lng = pos.coords.longitude;
                ensureMapReady();
                if (map) {
                    map.setView([lat, lng], 16);
                    placeMarker(lat, lng, true);
                }
            }, function(){
                alert('دسترسی به موقعیت رد شد یا ناموفق بود.');
            }, { enableHighAccuracy:true, timeout:10000 });
        });

        // همگام‌سازی با استپ ۸
        $('#address-dl').on('input change', function(){
            var t = $(this).val();
            $('#sd-address .detail').text(t || '—');
        });

        // وقتی Step‑7 فعال می‌شود، نقشه را آماده/ریفِرش کن
        var step7 = document.querySelector('.form--step.step-7');
        if (step7) {
            // تغییر کلاس/استایل (برای تب‌ها/آکاردئون‌ها)
            new MutationObserver(function(){
                if (step7.classList.contains('active') || step7.style.display !== 'none') {
                    setTimeout(ensureMapReady, 120);
                }
            }).observe(step7, { attributes: true, attributeFilter: ['class','style'] });

            // پایان انیمیشن ترنزیشن
            step7.addEventListener('transitionend', function(){
                setTimeout(ensureMapReady, 60);
            });
        }

        // بار اول اگر از همان ابتدا visible است
        $(ensureMapReady);

        // روی تغییر اندازه پنجره هم رفرش
        window.addEventListener('resize', function(){
            if (map) map.invalidateSize();
        });

    })(jQuery);
</script>


<?php wp_footer(); ?>
</body>
</html>
