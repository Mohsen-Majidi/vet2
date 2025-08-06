<?php
$base_url = plugins_url('/', VOS_DIR);
require_once __DIR__ . '/jdf.php';
?>
<script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>

<main
        class="container"
        id="root-container"
        data-step="0"
        data-step-state="fields"
>
    <!-- loading -->
    <div id="loading">
        <div class="container">
            <span>در حال بارگذاری …</span>
        </div>
    </div>
    <!-- Step & progress bar -->
    <div class="step-progress-bar">
        <div class="container">
            <progress id="progress-bar" value="10" max="100"></progress>
            <div class="step-bar">
                <div class="step current">
              <span class="icon step--icon">
                <svg width="24" height="24" aria-hidden="true">
                    <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i1'); ?>"></use>
                </svg>
              </span>
                    <p class="step--text">انتخاب سرویس</p>
                </div>
                <div class="step">
              <span class="icon step--icon">
                <svg width="24" height="24" aria-hidden="true">

                     <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i2'); ?>"></use>
                </svg>
              </span>
                    <p class="step--text">ثبت اطلاعات</p>
                </div>
                <div class="step">
              <span class="icon step--icon">
                <svg width="24" height="24" aria-hidden="true">
                    <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i3'); ?>"></use>
                </svg>
              </span>
                    <p class="step--text">تایید و ارسال</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Form steps -->
    <div class="form-steps">
        <form class="container">
            <!-- Step Index 0 -- select pet size -->
            <section class="form--step current step-0" data-step="0">
                <div class="container">
                    <h1>فقط چند سوال با اومدن دامپزشک دم خونتون فاصله دارین.</h1>
                    <p class="step--description">
                        با جواب دادن به چند سوال ساده زیر، درخواست رزروتون رو نهایی
                        کنین.
                    </p>
                    <h2 class="step--title">1. نوع پتتون رو انتخاب کنین</h2>
                    <div class="step--options">
                        <div class="container">
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pet-type"
                                                value="dog"
                                                id="dog-pet-radio"
                                                required
                                                checked
                                        />
                                        <span class="marker"></span>
                                        <span class="text">سگ</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pet-type"
                                                value="cat"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">گربه</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pet-type"
                                                value="bird"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">پرنده</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pet-type"
                                                value="rodent"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">
                          جوندگان کوچک(خرگوش، همستر، لاکپشت و…)
                        </span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pet-type"
                                                value="other"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">سایر (در توضیحات نوشته شود)</span>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 1 -- select pet size -->
            <section class="form--step step-1" data-step="1">
                <div class="container">
                    <h2 class="step--title">
                        اندازه سگتون رو انتخاب کنین(هر جثه‌ای به تجهیزات خاصی نیاز داره)
                    </h2>
                    <p class="step--description">
                        اگه چند تا سگ دارین می‌تونین چند گزینه‌ رو انتخاب کنین.
                    </p>
                    <div class="step--options">
                        <div class="container">
                            <div class="checkbox-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="checkbox"
                                                tabindex="-1"
                                                name="pet-size"
                                                value="small"
                                        />
                                        <span class="text">کوچک و عروسکی</span>
                                    </label>
                                </button>
                            </div>
                            <div class="checkbox-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="checkbox"
                                                tabindex="-1"
                                                name="pet-size"
                                                value="medium"
                                                checked
                                        />
                                        <span class="text">متوسط</span>
                                    </label>
                                </button>
                            </div>
                            <div class="checkbox-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="checkbox"
                                                tabindex="-1"
                                                name="pet-size"
                                                value="large"
                                        />
                                        <span class="text">بزرگ</span>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 2 -- select number of pets -->
            <section class="form--step step-2" data-step="2">
                <div class="container">
                    <h2 class="step--title">
                        2. تعداد پت‌هایی که دارین رو بهمون بگین
                    </h2>
                    <p class="step--description">
                        این کار به تیم دامپزشکان کمک می‌کنه بدونن چه مقدار وسایل مورد
                        نیازه.
                    </p>
                    <div class="step--options">
                        <div class="container">
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pets-number"
                                                value="1"
                                                required
                                                checked
                                        />
                                        <span class="marker"></span>
                                        <span class="text">1</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pets-number"
                                                value="2"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">2</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pets-number"
                                                value="3"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text">3</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-1">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="pets-number"
                                                value=">=4"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="text"> 4 و بیشتر </span>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 3 -- select number of pets -->
            <section class="form--step step-3" data-step="3">
                <div class="container">
                    <h2 class="step--title">3. نوع خدماتتون رو انتخاب کنین</h2>
                    <p class="step--description">
                        بعد از ثبت، می‌تونین خدمات بیشتری رو از طریق پروفایل خودتون
                        اضافه کنین.
                    </p>
                    <div class="step--options">
                        <div class="container">
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                معاینه، تشخیص و آزمایش
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">
                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="checkup-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    چکاپ و ویزیت توسط دامپزشک
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="health-certificate-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    صدور گواهی سلامت
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="passport-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    صدور شناسنامه پاسپورتی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="sampling-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    نمونه‌گیری برای آزمایشات مختلف
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="cbc-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    آزمایش خون کامل (CBC)
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="diagnosis-kit-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    کیت تشخیص بیماری‌ها
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                تزریقات و درمان
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">
                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="intramuscular-injection-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    تزریق عضلانی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="subcutaneous-injection-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    تزریق زیر پوستی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="intravenous-injection-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    تزریق وریدی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="angioket-brokerage-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    کارگزاری آنژیوکت
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="serum-therapy-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    سرم‌تراپی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="wound-dressing-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    پانسمان و مدیریت زخم
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                واکسن و پیشگیری
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">

                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="multiple-vaccines-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    واکسن چندگانه
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="rabies-vaccine-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    واکسن هاری
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="internal-parasitotherapy-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    انگل‌تراپی داخلی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="external-parasitotherapy-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    انگل‌تراپی خارجی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="microchip-implantation"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    کاشت میکروچیپ
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                بهداشت و رسیدگی
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">
                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="nail-trimming-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    کوتاهی ناخن
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="analsac-emptying-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    تخلیه کیسه مقعدی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="brushing-teeth-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    آموزش مسواک زدن و انجام آن
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="Eareye-cleaning-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    آموزش تمیز کردن گوش و چشم
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                تغذیه و مشاوره
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">
                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="food-diet-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    دادن رژیم غذایی
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="various-trainings-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    آموزش‌های مختلف به صاحبان پت‌ها
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="checkbox-type-2">
                                <div class="holder">
                                    <button type="button" class="label-btn">
                                        <input
                                                type="checkbox"
                                                name="useless"
                                                class="master-checkbox"
                                        />
                                        <div class="details">
                                            <p class="label-text">
                                                مراقبت و همراهی
                                                <span class="counter-container">
                              (<span class="checked-counter">2</span>)
                            </span>
                                            </p>
                                            <span class="start-from">از </span>
                                            <p class="price">200,000 تومان</p>
                                            <svg aria-hidden="true" class="dropdown-icon">
                                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i4'); ?>"></use>
                                            </svg>
                                        </div>
                                    </button>
                                    <ul class="options">
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="dog-walking-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    گرداندن سگ
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input
                                                        type="checkbox"
                                                        name="pet-nurse-service"
                                                        data-price="200000"
                                                />
                                                <p>
                                                    نگهداری از سگ و گربه (پت نرس)
                                                    <strong class="price">200,000 تومان</strong>
                                                </p>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 4 -- select number of pets -->
            <section class="form--step step-4" data-step="4">
                <div class="container">
                    <h2 class="step--title">
                        4. نکته خاصی اگه مدنظرتونه که فکر می‌کنین بهتره دامپزشکان بدونن
                        اینجا بنویسین.(اختیاری)
                    </h2>
                    <div class="step--options">
                        <div class="container">
                            <div class="textarea-type-1">
                    <textarea
                            name="notes"
                            rows="8"
                            placeholder="مثلا: نژاد پتتون، سابقه بیماری، سنشون و …"
                    ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 5 -- select number of pets -->
            <section class="form--step step-5" data-step="5">
                <div class="container">
                    <h2 class="step--title">
                        5. چه زمانی دامپزشکان پت بوم پیشتون بیان؟
                    </h2>
                    <p class="step--description">
                        رزرو سرویس باید حداقل 3 ساعت قبل از بازه انتخابی باشه.
                    </p>
                    <div class="step--options xscroll date-options">
                        <div class="container no-scrollbar dragging-scroller">
                            <?php
                            require_once __DIR__ . '/jdf.php'; // اگر در همین فایل نیست مسیر رو تنظیم کن

                            $days = 15;
                            $now = time();
                            for ($i = 0; $i < $days; $i++) {
                                $ts = strtotime("+$i day", $now);

                                $j_y = jdate('Y', $ts);
                                $j_m = jdate('n', $ts);
                                $j_d = jdate('j', $ts);
                                $week = jdate('W', $ts); // نام روز هفته
                                $monthfa = jdate('F', $ts); // نام ماه

                                $is_today = ($i === 0);
                                $is_tomorrow = ($i === 1);

                                $text = $is_today ? 'امروز' : ($is_tomorrow ? 'فردا' : $week);

                                $val = $j_d . '-' . $j_m;
                                ?>
                                <div class="radio-type-2">
                                    <button type="button">
                                        <label>
                                            <input
                                                    type="radio"
                                                    name="reservation-date"
                                                    value="<?php echo esc_attr($val); ?>"
                                                    data-week="<?php echo esc_attr($week); ?>"
                                                    data-day="<?php echo esc_attr($j_d); ?>"
                                                    data-monthfa="<?php echo esc_attr($monthfa); ?>"
                                                <?php if ($is_today) echo 'checked'; ?>
                                                    style="appearance: none !important;
                                                    -webkit-appearance: none !important;
                                                    -moz-appearance: none !important;
                                                    display: none !important;"/>
                                            <p class="text"><?php echo esc_html($text); ?></p>
                                            <p class="date-day"><?php echo esc_html($j_d); ?></p>
                                        </label>
                                    </button>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                    </div>
                    <div class="step--options time-options">
                        <div class="container">
                            <div class="radio-type-3">
                                <button type="button" disabled>
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="reservation-time"
                                                value="8:00-11:00"
                                                data
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="dis-text">رزرو شده</span>
                                        <span class="time-text">8:00 - 11:00</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-3">
                                <button type="button" disabled>
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="reservation-time"
                                                value="11:00-14:00"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="dis-text">رزرو شده</span>
                                        <span class="time-text">11:00 - 14:00</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-3">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="reservation-time"
                                                value="14:00-17:00"
                                                checked
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="dis-text">رزرو شده</span>
                                        <span class="time-text">14:00 - 17:00</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-3">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="reservation-time"
                                                value="17:00-20:00"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="dis-text">رزرو شده</span>
                                        <span class="time-text">17:00 - 20:00</span>
                                    </label>
                                </button>
                            </div>
                            <div class="radio-type-3">
                                <button type="button">
                                    <label class="label">
                                        <input
                                                type="radio"
                                                name="reservation-time"
                                                value="20:00-23:00"
                                                required
                                        />
                                        <span class="marker"></span>
                                        <span class="dis-text">رزرو شده</span>
                                        <span class="time-text">20:00 - 23:00</span>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Step Index 6 -- Login step (only for non-logged in users) -->
            <?php if (!is_user_logged_in()) : ?>
                <section class="form--step step-20" data-step="20">
                    <div class="container">
                        <h2 class="step--title">6. لطفاً ابتدا وارد سایت شوید.</h2>
                        <div class="step--options">
                            <div class="container">
                                <div class="grid-fields-type-1">

                                    <!-- فقط یک دکمه ورود/ثبت‌نام -->
                                    <span class="digits-login-modal">ورود / ثبت نام</span>

                                    <!-- فقط یک div برای نمایش فرم دیجیتس -->
                                    <div id="dm-content" style="display:none">
                                        <?php echo do_shortcode('[dm-page]'); ?>
                                    </div>

                                    <script>
                                        // بازکردن فرم
                                        document.querySelector('.open-login-box').onclick = () =>
                                            document.getElementById('dm-content').style.display = 'block';

                                        // بستن فرم بعد از لاگین موفق
                                        document.addEventListener('digits_login_success', () => {
                                            const box = document.getElementById('dm-content');
                                            if (box) box.remove();        // یا box.style.display = 'none';

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
                                    </script>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <script>
                    (function () {
                        function activateStep(step) {
                            document.querySelectorAll('.form--step').forEach(el =>
                                el.classList.remove('current')
                            );
                            const target = document.querySelector(`.form--step[data-step="${step}"]`);
                            if (target) target.classList.add('current');

                            const loginBox = document.getElementById('dm-content');
                            if (loginBox) loginBox.remove();

                            // به‌روزرسانی main dataset
                            const main = document.querySelector('main.container');
                            if (main) {
                                main.dataset.step = step;
                                main.dataset.stepState = "def";
                            }
                        }

                        ['digits_user_logged_in', 'digitLoggedIn', 'digits_login_success']
                            .forEach(evt => document.addEventListener(evt, () => {
                                // به جای رفتن به استپ 7، از تابع navigateAfterLogin استفاده کنیم
                                if (typeof window.navigateAfterLogin === 'function') {
                                    window.navigateAfterLogin();
                                } else {
                                    activateStep(6);
                                }
                            }));
                    })();
                </script>
            <?php endif; ?>


            <!-- Step Index 6/7 -- User information (step 6 for logged in users, step 7 for non-logged in users) -->
            <section class="form--step <?php echo is_user_logged_in() ? 'step-6' : 'step-7'; ?>" data-step="<?php echo is_user_logged_in() ? '6' : '7'; ?>">
                <div class="container">
                    <h2 class="step--title">6. لطفاً اطلاعات زیر رو تکمیل کنین.</h2>
                    <div class="step--options">
                        <div class="container">
                            <div class="grid-fields-type-1">
                                <div class="holder field-type-1">
                                    <label for="name">
                                        نام و نام خانوادگی
                                        <span class="required">*</span>
                                    </label>
                                    <input
                                            type="text"
                                            name="name"
                                            id="name"
                                            autocomplete="on"
                                            placeholder="مثال: آرمان بزرگی"
                                            value="<?php echo is_user_logged_in() ? esc_attr(get_user_meta(get_current_user_id(), 'first_name', true) . ' ' . get_user_meta(get_current_user_id(), 'last_name', true)) : ''; ?>"
                                            required
                                    />
                                </div>
                                <div class="holder field-type-1">
                                    <label for="pet-name"> نام پت(اختیاری) </label>
                                    <input
                                            type="text"
                                            name="pet-name"
                                            id="pet-name"
                                            placeholder="مثال: رکسی"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 7/8 -- Address selection -->
            <section class="form--step <?php echo is_user_logged_in() ? 'step-7' : 'step-8'; ?>" data-step="<?php echo is_user_logged_in() ? '7' : '8'; ?>">
                <div class="container">
                    <h2 class="step--title">7. دامپزشکان پت بوم کجا باید بیان؟</h2>
                    <p class="step--description">
                        سرویس دامپزشک سیار تنها در مناطق 22 گانه تهران فعلا فعاله!
                    </p>
                    <div class="step--options">
                        <button type="button" id="add-new-address">
                            <svg aria-hidden="true">
                                <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#plus'); ?>"></use>
                            </svg>
                            <span>افزودن آدرس جدید</span>
                        </button>
                        <div class="container">


                        </div>
                        <div class="address-fields">
                            <div class="con">
                                <div class="map">
                                    <div class="container">

                                        <div id="vos-map"
                                             style="height: 380px; border-radius: 12px; overflow: hidden;"></div>

                                        <div class="vos-map-actions"
                                             style="margin-top:10px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                            <button type="button" class="find-my-loc" id="find-my-loc-btn">
                                                <span>موقعیت من</span>
                                                <svg aria-hidden="true">
                                                    <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i9'); ?>"></use>
                                                </svg>
                                            </button>
                                            <small id="vos-address-hint" style="color:#ae7400;"></small>
                                        </div>


                                        <div class="holder field-type-1">
                                            <label for="address-name"> نام آدرس </label>
                                            <input
                                                    type="text"
                                                    name="address-name"
                                                    id="address-name"
                                                    autocomplete="off"
                                                    placeholder="مثال: خانه"
                                            />
                                        </div>
                                        <div class="holder-row" style="display: flex;gap: 1rem;margin: 8px 0 8px 0;">
                                            <div class="holder field-type-1">
                                                <label for="address-province">استان</label>
                                                <input
                                                        type="text"
                                                        name="address-province"
                                                        id="address-province"
                                                        autocomplete="off"
                                                        value="تهران"
                                                        disabled
                                                />
                                            </div>
                                            <div class="holder field-type-1" style="flex: 1">
                                                <label for="address-city">شهر</label>
                                                <input
                                                        type="text"
                                                        name="address-city"
                                                        id="address-city"
                                                        autocomplete="off"
                                                        value="تهران"
                                                        disabled
                                                />
                                            </div>
                                        </div>

                                        <div class="holder field-type-1">
                                            <label for="address-dl">
                                                جزئیات آدرس <span class="required">*</span>
                                            </label>
                                            <div class="textarea-type-1">
                                          <textarea
                                                  name="address-dl"
                                                  id="address-dl"
                                                  rows="5"
                                                  placeholder="مثال: خیابان آزادی، بعد از هتل نخل جنب پیتزا cc، پلاک 22 "
                                          ></textarea>
                                            </div>
                                        </div>

                                        <input type="hidden" id="vos-lat" name="latitude">
                                        <input type="hidden" id="vos-lng" name="longitude">

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Step Index 8/9 -- Order details -->
            <section class="form--step <?php echo is_user_logged_in() ? 'step-8' : 'step-9'; ?>" data-step="<?php echo is_user_logged_in() ? '8' : '9'; ?>">
                <div class="container">
                    <h2 class="step--title">8. جزئیات سفارش</h2>
                    <p class="step--description">
                        اینم از این، حالا می‌تونین درخواستتون رو در یک نگاه ببینین و اگه
                        نیاز به تغییر داره اصلاحش کنین.
                    </p>
                    <div class="details">
                        <div class="row" id="sd-petinfo">
                            <span class="label">نوع و تعداد پت</span>
                            <p class="detail"></p>
                        </div>
                        <div class="row" id="sd-requsted-items">
                            <span class="label">موارد درخواستی</span>
                            <p class="detail"></p>
                        </div>
                        <div class="row" id="sd-reservation-date">
                            <span class="label">زمان رزرو شده</span>
                            <p class="detail"></p>
                        </div>
                        <div class="row" id="sd-address">
                            <span class="label">آدرس</span>
                            <p class="detail"></p>
                        </div>
                        <div class="row" id="sd-cost">
                            <span class="label">هزینه تخمینی سفارش</span>
                            <p class="detail"></p>
                        </div>
                    </div>
                    <div class="discount-input-sec">
                        <input
                                type="text"
                                name="discount-code"
                                id="discount-code"
                                placeholder="کد تخفیف را اینجا وارد کنین؟"
                        />
                        <button type="button" id="apply-discount-code">
                            اعمال کد تخفیف
                        </button>
                    </div>
                </div>
            </section>
            <!-- Step Index 9/10 -- Order completion -->
            <section class="form--step <?php echo is_user_logged_in() ? 'step-9' : 'step-10'; ?>" data-step="<?php echo is_user_logged_in() ? '9' : '10'; ?>">
                <div class="container">
                    <img
                            class="done--img"
                            src="<?php echo esc_url($base_url . 'assets/images/done.webp'); ?>"
                            alt="سفارش شما با موفقیت ثبت شد"
                            width="360"
                            height="360"
                    />
                    <h2 class="done--title">سفارش شما با موفقیت ثبت شد</h2>
                    <p class="done--description">
                        نهایتا تا <strong>30 دقیقه</strong> آینده سفارش توسط دامپزشک
                        تایید می‌شود.
                    </p>
                    <div class="done--progress">
                        <div class="done--step">
                  <span class="icon">
                    <svg aria-hidden="true">

                            <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i6'); ?>"></use>

                    </svg>
                  </span>
                            <p>تایید دامپزشک</p>
                        </div>
                        <span class="filler-line"></span>
                        <div class="done--step">
                  <span class="icon">
                    <svg aria-hidden="true">

                         <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i7'); ?>"></use>
                    </svg>
                  </span>
                            <p>تایید دامپزشک</p>
                        </div>
                        <span class="filler-line"></span>
                        <div class="done--step">
                  <span class="icon">
                    <svg aria-hidden="true">

                         <use href="<?php echo esc_url($base_url . 'assets/icons/pack.svg#i8'); ?>"></use>
                    </svg>
                  </span>
                            <p>تایید دامپزشک</p>
                        </div>
                    </div>
                    <div class="done--details">
                        <div class="row">
                            <span class="label">نوع خدمات:</span>
                            <p class="detail">خدمات دامپزشکی در محل تهران</p>
                        </div>
                        <div class="row">
                            <span class="label">کد پیگیری سفارش:</span>
                            <p class="detail">123456789</p>
                        </div>
                        <div class="row">
                            <span class="label">وضعیت:</span>
                            <p class="detail"><strong>در انتظار تایید دامپزشک</strong></p>
                        </div>
                    </div>
                    <button type="button" id="redirect-to-panel-btn">
                        <p>مشاهده جزئیات سفارش</p>
                        <span class="countdown">(10)</span>
                    </button>
                </div>
            </section>
        </form>
    </div>
    <!-- Form step navigation -->
    <div class="form-step-nav">
        <div class="container">
            <div class="cost">
                <p>هزینه تقریبی فرایند:</p>
                <p class="price">0 تومان</p>
            </div>
            <div class="date">
                <p>زمان انتخابی شما:</p>
                <p class="time"></p>
            </div>
            <div class="master-buttons">
                <button type="button" class="btn prev order-1" id="prev-step-btn">
                    <span>بازگشت</span>
                    <span class="final-step">اصلاح فرم</span>
                </button>
                <button type="button" class="btn next order-2" id="next-step-btn">
                    <span>مرحله بعد</span>
                    <span class="final-step">تایید سفارش</span>
                    <span class="on-7-txt">ثبت آدرس</span>
                    <span class="login-txt">ورود</span>
                    <span class="register-txt">ثبت نام در پت بوم</span>
                    <span class="otp-txt">تایید کد</span>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>



