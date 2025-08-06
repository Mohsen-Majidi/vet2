const nextBtn = document.getElementById("next-step-btn");
const prevBtn = document.getElementById("prev-step-btn");
// const formSteps = document.querySelectorAll(".form--step");
let formSteps = Array.from(document.querySelectorAll(".form--step"));
const progressBar = document.getElementById("progress-bar");
const main = document.getElementById("root-container");
const stepProgress = document.querySelector(".step-progress-bar");

const dogRadio = document.getElementById("dog-pet-radio");
const countdownElement = document.querySelector(
    "#redirect-to-panel-btn .countdown"
);
const redirectButton = document.getElementById("redirect-to-panel-btn");

const addAdressBtn = document.getElementById("add-new-address");

let seconds = 10;
let mainStep = 0;


const stepsDetails = {
  0: { progressValue: 11 },
  1: { progressValue: 18 },
  2: { progressValue: 23 },
  3: { progressValue: 32 },
  4: { progressValue: 40 },
  5: { progressValue: 44 },
  6: { progressValue: 75 },
  7: { progressValue: 90 },
  8: { progressValue: 100 },
  9: { progressValue: 100 },
};

const user = {
  isLogin: false,
  action: "",
};

// Refresh form steps after DOM updates (e.g., after login removes a step)
const refreshFormSteps = () => {
  formSteps = Array.from(document.querySelectorAll('.form--step'));

  // Use helper function to check and fix step state
  checkAndFixStepState();

  // Update step details if needed
  if (stepsDetails && Object.keys(stepsDetails).length > 0) {
    // Ensure we have progress values for all steps
    formSteps.forEach(step => {
      const stepIndex = parseInt(step.dataset.step);
      if (!stepsDetails[stepIndex]) {
        console.warn(`No progress details for step ${stepIndex}`);
      }
    });
  }
};

// Expose refresh function globally so other scripts can call it
window.refreshFormSteps = refreshFormSteps;

// Helper function to check and fix step state
const checkAndFixStepState = () => {
  const currentStep = document.querySelector('.form--step.current');
  const allSteps = document.querySelectorAll('.form--step');

  const resolveStep6 = () => {
    let step6El = document.querySelector('.form--step[data-step="6"]');
    if (!step6El) {
      const step7El = document.querySelector('.form--step[data-step="7"]');
      if (step7El) {
        step7El.dataset.step = '6';
        step7El.className = step7El.className.replace(/step-7/g, 'step-6');
        step6El = step7El;
      }
    }
    return step6El;
  };

  console.log('Checking step state:', {
    currentStep: currentStep ? currentStep.dataset.step : 'none',
    totalSteps: allSteps.length,
    steps: Array.from(allSteps).map(s => ({ step: s.dataset.step, current: s.classList.contains('current') })),
    lastLoginStep: lastLoginStep,
    shouldNavigateToStep6: shouldNavigateToStep6
  });

  // Check if URL contains a step parameter and navigate accordingly
  const urlParams = new URLSearchParams(window.location.search);
  const urlStep = urlParams.get('step');
  if (urlStep) {
    const urlStepElement = document.querySelector(`.form--step[data-step="${urlStep}"]`);
    if (urlStepElement) {
      // Remove current class from all steps
      document.querySelectorAll('.form--step').forEach(s => {
        s.classList.remove('current');
      });

      // Set the URL-specified step as current
      urlStepElement.classList.add('current');

      // Update main dataset
      if (main) {
        main.dataset.step = urlStep;
        main.dataset.stepState = "def";
      }

      // Remove step param from URL without reloading
      urlParams.delete('step');
      const newUrl = `${window.location.pathname}${urlParams.toString() ? '?' + urlParams.toString() : ''}${window.location.hash}`;
      window.history.replaceState({}, '', newUrl);

      return urlStepElement;
    }
  }

  // بررسی sessionStorage برای مرحله حفظ شده
  const shouldNavigate = sessionStorage.getItem('shouldNavigateToStep6');
  if (shouldNavigate === 'true') {
    console.log('Found preserved step in sessionStorage, navigating to step 6');
    sessionStorage.removeItem('shouldNavigateToStep6');
    shouldNavigateToStep6 = true;
  }

  // اگر باید به مرحله 6 برویم
  if (shouldNavigateToStep6) {
    // const step6Element = document.querySelector('.form--step[data-step="6"]');
    const step6Element = resolveStep6();
    if (step6Element) {
      console.log('Navigating to step 6 after reload');
      
      // حذف کلاس current از تمام مراحل
      document.querySelectorAll('.form--step').forEach(s => {
        s.classList.remove('current');
      });
      
      // تنظیم مرحله 6 به عنوان فعلی
      step6Element.classList.add('current');
      
      // Update main dataset
      if (main) {
        main.dataset.step = '6';
        main.dataset.stepState = "def";
      }
      
      shouldNavigateToStep6 = false;
      return step6Element;
    }
  }

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
      // const step6Element = document.querySelector('.form--step[data-step="6"]');
      const step6Element = resolveStep6();
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

// Expose helper function globally
window.checkAndFixStepState = checkAndFixStepState;

// Global variable to store current step
let preservedStep = null;
let isLoginInProgress = false; // Flag to prevent step reset during login
let lastLoginStep = null; // Store the step before login
let shouldNavigateToStep6 = false; // Flag to navigate to step 6 after reload

// Initialize step state when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM loaded, checking step state...');
  
  // فقط اگر هیچ مرحله‌ای فعلی نباشد، بررسی کن
  const currentStep = document.querySelector('.form--step.current');
  if (!currentStep) {
    console.log('No current step found, checking step state...');
    checkAndFixStepState();
  } else {
    console.log('Current step already exists:', currentStep.dataset.step);
  }
});

// Also check when window loads (in case DOMContentLoaded already fired)
window.addEventListener('load', () => {
  console.log('Window loaded, checking step state...');
  
  // اگر لاگین در حال انجام است، مرحله را ریست نکن
  if (isLoginInProgress) {
    console.log('Login in progress, skipping step reset');
    return;
  }
  
  // بررسی sessionStorage برای مرحله حفظ شده
  const shouldNavigate = sessionStorage.getItem('shouldNavigateToStep6');
  if (shouldNavigate === 'true') {
    console.log('Found preserved step in sessionStorage, navigating to step 6');
    sessionStorage.removeItem('shouldNavigateToStep6');
    shouldNavigateToStep6 = true;
  }
  
  // اگر باید به مرحله 6 برویم
  if (shouldNavigateToStep6) {
    const step6Element = document.querySelector('.form--step[data-step="6"]');
    if (step6Element) {
      console.log('Navigating to step 6 after reload');
      
      // حذف کلاس current از تمام مراحل
      document.querySelectorAll('.form--step').forEach(s => {
        s.classList.remove('current');
      });
      
      // تنظیم مرحله 6 به عنوان فعلی
      step6Element.classList.add('current');
      
      // Update main dataset
      if (main) {
        main.dataset.step = '6';
        main.dataset.stepState = "def";
      }
      
      shouldNavigateToStep6 = false;
      return;
    }
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

// Add error handling for step navigation
window.addEventListener('error', function(e) {
  if (e.message.includes('classList') || e.message.includes('null')) {
    console.error('Step navigation error detected:', e.message);
    // Reset form state after a short delay
    setTimeout(() => {
      window.resetFormState();
    }, 100);
  }
});

// تابع کمکی برای حفظ مرحله قبل از رفرش (فقط برای لاگین دیجیتس)
window.preserveStepBeforeReload = function() {
  console.log('Preserving step before reload...');
  shouldNavigateToStep6 = true;
  
  // ذخیره در sessionStorage
  sessionStorage.setItem('shouldNavigateToStep6', 'true');
  sessionStorage.setItem('lastLoginStep', '6');
  
  console.log('Step preserved for after reload');
};

// next button navigation
nextBtn.addEventListener("click", () => {
  console.log('Next button clicked');
  const currentStep = checkAndFixStepState();
  if (!currentStep) {
    console.error("No steps found at all");
    return;
  }
  const navDirection = "next";
  const stepIndex = currentStep.dataset.step;

  console.log('Navigating from step', stepIndex, 'to next step');
  console.log('Current step element:', currentStep);
  console.log('All available steps:', Array.from(document.querySelectorAll('.form--step')).map(s => ({ step: s.dataset.step, current: s.classList.contains('current') })));
  
  stepNavigationHandle(currentStep, +stepIndex, navDirection);
});

// prev button navigation
prevBtn.addEventListener("click", () => {
  console.log('Prev button clicked');
  const currentStep = checkAndFixStepState();
  if (!currentStep) {
    console.error("No steps found at all");
    return;
  }
  const navDirection = "prev";
  const stepIndex = currentStep.dataset.step;

  console.log('Navigating from step', stepIndex, 'to previous step');
  console.log('Current step element:', currentStep);
  console.log('All available steps:', Array.from(document.querySelectorAll('.form--step')).map(s => ({ step: s.dataset.step, current: s.classList.contains('current') })));
  
  stepNavigationHandle(currentStep, +stepIndex, navDirection);
});

// navigation between steps handle
const stepNavigationHandle = (currentStep, currentStepIndex, direction) => {
  console.log(`stepNavigationHandle called: currentStepIndex=${currentStepIndex}, direction=${direction}`);
  console.log('formSteps.length:', formSteps.length);
  
  if (direction == "next" && currentStepIndex + 1 == formSteps.length) {
    console.log('Reached last step, cannot go next');
    return;
  }
  if (direction == "prev" && currentStepIndex - 1 < 0) {
    console.log('Reached first step, cannot go prev');
    return;
  }

  if (currentStepIndex == 0) {
    let nextStep = 2;
    dogRadio.checked && (nextStep = 1);
    console.log(`Step 0: going to step ${nextStep}`);
    toStep(currentStepIndex, nextStep, direction);
  } else if (currentStepIndex == 2 && direction == "prev") {
    let prevStep = 0;
    dogRadio.checked && (prevStep = 1);
    console.log(`Step 2: going to step ${prevStep}`);
    toStep(currentStepIndex, prevStep, direction);
  } else if (currentStepIndex == 5 && direction == "next") {
    const nextStep = getNextStepAfter5();
    console.log(`Step 5: next step determined as ${nextStep}`);

    if (nextStep !== null) {
      toStep(currentStepIndex, nextStep, direction);
    } else {
      // اگر نتوانستیم مرحله بعدی را تعیین کنیم، اولین مرحله موجود را پیدا کنیم
      const fallbackStep = findNextAvailableStep(currentStepIndex);
      if (fallbackStep !== null) {
        console.log(`Using fallback step: ${fallbackStep}`);
        toStep(currentStepIndex, fallbackStep, direction);
      } else {
        console.warn('No available steps found after step 5, staying on current step');
      }
    }
  }
  else if (currentStepIndex == 6 && direction == "next") {
    // After login step, check if user is logged in
    if (window.vosUserData && window.vosUserData.user_id) {
      // User is logged in, go to next step
      console.log('User is logged in at step 6, proceeding to step 7');
      toStep(currentStepIndex, currentStepIndex + 1, direction);
    } else {
      // User not logged in, stay on login step
      console.log('User not logged in, staying on login step');
      return;
    }
  }
  else if (currentStepIndex == 7 && main.dataset.stepState == "fields") {
    if (direction == "next"){
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

    }
    main.classList.add("loading");
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
  } else if (direction == "next") {
    console.log(`General next: going from ${currentStepIndex} to ${currentStepIndex + 1}`);
    toStep(currentStepIndex, currentStepIndex + 1, direction);
  } else if (direction == "prev") {
    console.log(`General prev: going from ${currentStepIndex} to ${currentStepIndex - 1}`);
    toStep(currentStepIndex, currentStepIndex - 1, direction);
  } else {
    console.log('No navigation action taken');
    return;
  }
};

// change step func
const toStep = (from, to, direction) => {
  const currentStep = document.querySelector(
      `.form--step[data-step="${from}"]`
  );
  const nextStep = document.querySelector(`.form--step[data-step="${to}"]`);

  console.log(`toStep called: from ${from} to ${to}, direction: ${direction}`);

  // بررسی وجود عناصر
  if (!currentStep) {
    console.error(`Step ${from} not found`);
    console.log('Available steps:', Array.from(document.querySelectorAll('.form--step')).map(s => s.dataset.step));
    return;
  }

  if (!nextStep) {
    console.error(`Step ${to} not found`);
    console.log('Available steps:', Array.from(document.querySelectorAll('.form--step')).map(s => s.dataset.step));
    return;
  }

  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });

  currentStep.classList.remove("current");
  nextStep.classList.add("current");

  if (direction == "next") {
    currentStep.dataset.animation = "center-to-right";
    nextStep.dataset.animation = "left-to-center";
  } else {
    currentStep.dataset.animation = "center-to-left";
    nextStep.dataset.animation = "right-to-center";
  }

  console.log(`Successfully navigated from step ${from} to step ${to}`);
  applyStepFuncs(to);
};

// useState for step changes
const applyStepFuncs = (step) => {
  // Update progress bar
  progressValue = stepsDetails[step].progressValue;
  progressBar.value = progressValue;

  // change step progress
  const stepbarIndex = step > 7 ? 2 : step > 5 ? 1 : 0;
  const progressbarSteps = stepProgress.querySelectorAll(".step");
  const isCurrentRight =
      progressbarSteps[stepbarIndex].classList.contains("current");

  if (!isCurrentRight) {
    const currentStepbar = stepProgress.querySelector(".step.current");
    currentStepbar.classList.remove("current");
    progressbarSteps[stepbarIndex].classList.add("current");
  }

  main.dataset.step = step;
  main.dataset.stepState = "def";
  mainStep = step;

  disableButtonCan();

  // set Step 7 details
  step == 8 && setFinalDestails();

  // step 9 redirect
  step == 9 && finishCountdown();
};

// disabling naxt button in requied
const step1Checkboxs = Array.from(
    formSteps[1].querySelectorAll('input[type="checkbox"]')
);
const requiedNameField = document.getElementById("name");
const requiedAddressField = document.getElementById("address-dl");

const disableButtonCan = () => {
  if (mainStep == 1) {
    const isChecked = step1Checkboxs.some((cBox) => cBox.checked);
    !isChecked && dogRadio.checked
        ? (nextBtn.disabled = true)
        : (nextBtn.disabled = false);
  } else if (mainStep == 3) {
    const checkedCB = formSteps[3].querySelector(
        ".checkbox-type-2 input:checked"
    );
    checkedCB ? (nextBtn.disabled = false) : (nextBtn.disabled = true);
  } else if (mainStep == 6) {
    requiedNameField.value == ""
        ? (nextBtn.disabled = true)
        : (nextBtn.disabled = false);
  } else if (mainStep == 7) {
    if (main.dataset.stepState == "fields") {
      requiedAddressField.value == ""
          ? (nextBtn.disabled = true)
          : (nextBtn.disabled = false);
    } else {
      const selectedAddress = document.querySelector(
          'input[name="address"]:checked'
      );
      selectedAddress ? (nextBtn.disabled = false) : (nextBtn.disabled = true);
    }
  } else {
    nextBtn.disabled = false;
  }
};

step1Checkboxs.forEach((checkbox) => {
  checkbox.addEventListener("change", disableButtonCan);
});
requiedNameField.addEventListener("input", disableButtonCan);
requiedAddressField.addEventListener("input", disableButtonCan);

// get details

const getPetType = () => {
  const pettype = document
      .querySelector('.form--step.step-0 input[name="pet-type"]:checked')
      .parentNode.querySelector(".text").textContent;
  return pettype;
};

const getPetsNum = () => {
  const petsNumber = document.querySelector(
      '.form--step.step-2 input[name="pets-number"]:checked'
  ).value;
  return petsNumber;
};

const getRequstedServices = () => {
  const allCheckedServices = Array.from(
      document.querySelectorAll(".step-3 .checkbox-type-2 .options input:checked")
  );

  if (allCheckedServices.length) {
    const checkedServices = allCheckedServices.map((item) => {
      return item.parentNode.querySelector("p").firstChild.nodeValue.trim();
    });
    return checkedServices;
  } else {
    return ["هیج سرویسی انتخاب نشده است."];
  }
};

const getReservationDate = () => {
  const dateInp = document.querySelector(".date-options input:checked");
  const timeInp = document.querySelector(".time-options input:checked");

  const { week, monthfa, day } = dateInp.dataset;
  const timeRange = timeInp.value;
  const [from, to] = timeRange.split("-");
  const format = (time) =>
      time.split(":")[1] === "00" ? time.split(":")[0] : time;

  const formatedTimeString = `${week} ${day} ${monthfa} ساعت ${format(
      from
  )} تا ${format(to)}`;

  return formatedTimeString;
};

const getAddress = () => {
  const selectedAddress = document
      .querySelector('input[name="address"]:checked')
      .parentNode.querySelector(".text").textContent;

  return selectedAddress;
};

const getTotalCost = () => {
  const allCheckedServices = Array.from(
      document.querySelectorAll(".step-3 .checkbox-type-2 .options input:checked")
  );

  const total = allCheckedServices.reduce(
      (tot, item) => +item.dataset.price + tot,
      0
  );
  return total.toLocaleString("en-US");
};

const setFinalDestails = () => {
  const values = {
    petInfo: `${getPetType()} (${getPetsNum()} دونه)`,
    requestedItems: getRequstedServices().join(" - "),
    reservationDate: getReservationDate(),
    address: getAddress(),
    totalCost: getTotalCost(),
  };

  const sd = {
    petInfo: document.querySelector("#sd-petinfo .detail"),
    requestedItems: document.querySelector("#sd-requsted-items .detail"),
    reservationDate: document.querySelector("#sd-reservation-date .detail"),
    address: document.querySelector("#sd-address .detail"),
    totalCost: document.querySelector("#sd-cost .detail"),
  };

  Object.keys(values).forEach((key) => {
    sd[key].textContent = values[key];
  });
};

// done count down

const finishCountdown = () => {
  const countdown = setInterval(() => {
    seconds--;
    countdownElement.textContent = `(${seconds})`;

    if (seconds <= 0) {
      clearInterval(countdown);
      window.location.href = "https://google.com/";
    }
  }, 1000);
};

redirectButton.addEventListener("click", () => {
  window.location.href = "https://google.com/";
});

const setAddressFields = (props) => {
  const adName = document.getElementById("address-name");
  const adDl = document.getElementById("address-dl");

  props.name ? (adName.value = props.name) : (adName.value = "");
  props.dl ? (adDl.value = props.dl) : (adDl.value = "");
};

const showAddressFields = (props) => {
  setAddressFields(props);
  user.action = props.action;
  main.dataset.stepState = "fields";
  disableButtonCan();
};
addAdressBtn.addEventListener("click", () =>
    showAddressFields({ action: "add-address" })
);

const editAddress = (elem) => {
  const input = elem.querySelector("input");
  const data = JSON.parse(elem.querySelector(".address_meta_data").textContent);

  // Return address data for frontend developer to use
  const addressData = {
    id: data.id,
    address_name: data.address_name,
    address_city: data.address_city,
    address_province: data.address_province,
    address_dl: data.address_dl,
    latitude: data.latitude,
    longitude: data.longitude
  };

  console.log('Address data for map marker:', addressData);

  // Call a global function that frontend developer can implement
  if (typeof window.handleAddressEdit === 'function') {
    window.handleAddressEdit(addressData);
  }

  return addressData;
};

const successAACB = (list) => {
  main.classList.remove("loading");
  setAddressFields({ action: "" });

  const container = document.querySelector(
      ".step-7 .step--options > .container"
  );

  container.innerHTML = "";

  list.forEach((address) => {
    const addressElement = document.createElement("div");
    addressElement.className = "radio-type-4 address-radio";

    const scriptElement = document.createElement("script");
    scriptElement.className = "address_meta_data";
    scriptElement.type = "application/json";
    scriptElement.textContent = JSON.stringify(address);

    addressElement.innerHTML = `
      <button type="button" class="label-btn">
        <label class="label">
          <input
            type="radio"
            name="address"
            value="${address.id}"
            autocomplete="off"
            required
            ${address.id === list[0].id ? "checked" : ""}
          />
          <span class="marker"></span>
          <span class="text">${address.address_name}</span>
        </label>
      </button>
      <button
        class="edit-address-btn"
        type="button"
        onclick="editAddress(this.parentNode)"
      >
        <svg aria-hidden="true">
          <use href="${window.VOS?.url || ''}assets/icons/pack.svg#edit"></use>
        </svg>
      </button>
    `;

    addressElement.prepend(scriptElement);

    container.appendChild(addressElement);
  });

  main.dataset.stepState = "def";
  disableButtonCan();
};

const errorAACB = () => {
  main.classList.remove("loading");
};

// Make callbacks globally available
window.successAACB = successAACB;
window.errorAACB = errorAACB;

// تابع کمکی برای ریست کردن حالت فرم در صورت بروز مشکل
window.resetFormState = function() {
  console.log('Resetting form state...');

  // Refresh form steps
  refreshFormSteps();

  // Reset main dataset
  if (main) {
    const currentStep = document.querySelector('.form--step.current');
    if (currentStep) {
      main.dataset.step = currentStep.dataset.step;
      main.dataset.stepState = "def";
    }
  }

  // Re-enable buttons
  if (nextBtn) nextBtn.disabled = false;
  if (prevBtn) prevBtn.disabled = false;

  console.log('Form state reset completed');
};

// تابع کمکی برای پیدا کردن مرحله بعدی موجود
const findNextAvailableStep = (currentStepIndex) => {
  const allSteps = Array.from(document.querySelectorAll('.form--step'));

  // پیدا کردن مراحل موجود بعد از مرحله فعلی
  const availableSteps = allSteps
    .map(step => parseInt(step.dataset.step))
    .filter(stepIndex => stepIndex > currentStepIndex)
    .sort((a, b) => a - b);

  console.log('Available steps after current:', availableSteps);

  if (availableSteps.length > 0) {
    return availableSteps[0]; // اولین مرحله موجود
  }

  return null;
};

// تابع کمکی برای هدایت به مرحله بعدی بعد از لاگین موفق
window.navigateAfterLogin = function() {
    console.log('=== NAVIGATE AFTER LOGIN ===');
    
    const currentStep = document.querySelector('.form--step.current');
    if (!currentStep) {
        console.error('No current step found in navigateAfterLogin');
        return;
    }

    const step = currentStep.dataset.step;
    console.log('Current step in navigateAfterLogin:', step);
    console.log('Preserved step:', preservedStep);
    
    if (step === '20') {
        console.log('Digits auth succeeded at step20, going to step6');
        
        // حفظ مرحله قبل از رفرش احتمالی (فقط برای لاگین دیجیتس)
        if (typeof window.preserveStepBeforeReload === 'function') {
            window.preserveStepBeforeReload();
        }
        
        // قبل از انتقال، مطمئن شو که مرحله 6 وجود دارد
        const step6Element = document.querySelector('.form--step[data-step="6"]');
        if (step6Element) {
            console.log('Step 6 found, navigating...');
            
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
            
            // اجرای توابع مرحله
            if (typeof applyStepFuncs === 'function') {
                applyStepFuncs(6);
            }
            
            // جلوگیری از reset شدن مرحله در checkAndFixStepState
            lastLoginStep = '6';
            
            console.log('Successfully navigated to step 6');
        } else {
            console.error('Step 6 not found, cannot navigate');
        }
    }
    else if (step === '6') {
        console.log('Login succeeded at step6, going to step7');
        // قبل از انتقال، مطمئن شو که مرحله 7 وجود دارد
        const step7Element = document.querySelector('.form--step[data-step="7"]');
        if (step7Element) {
            console.log('Step 7 found, navigating...');
            toStep(6, 7, 'next');
        } else {
            console.error('Step 7 not found, cannot navigate');
        }
    }
    else {
        console.log('Unknown step for navigation:', step);
    }
    
    console.log('=== END NAVIGATE AFTER LOGIN ===');
};


// تابع کمکی برای بررسی وضعیت لاگین و تعیین مرحله بعدی
const getNextStepAfter5 = () => {
    const step20 = document.querySelector('.form--step[data-step="20"]');
    const step6  = document.querySelector('.form--step[data-step="6"]');
    const step7  = document.querySelector('.form--step[data-step="7"]');

    // اگر کاربر لاگین نیست و مرحله‌ی 20 وجود دارد، برو به 20
    if (!window.isUserLoggedIn() && step20) {
        return 20;
    }
    // اگر کاربر لاگین است و مرحله‌ی 6 هست، برو به 6
    if (window.isUserLoggedIn() && step6) {
        return 6;
    }
    // در غیر این صورت اگر مرحله‌ی 7 وجود دارد (مثلاً فرم اطلاعات کاربر برای مهمان) برگردانش
    if (step7) {
        return 7;
    }
    console.warn('No steps found after step 5');
    return null;
};


// تابع کمکی برای بررسی وضعیت لاگین کاربر
window.isUserLoggedIn = function() {
  return !!(window.vosUserData && window.vosUserData.user_id);
};

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

// تابع کمکی برای حفظ مرحله قبل از رفرش
window.preserveStepBeforeReload = function() {
  console.log('Preserving step before reload...');
  shouldNavigateToStep6 = true;
  
  // ذخیره در sessionStorage
  sessionStorage.setItem('shouldNavigateToStep6', 'true');
  sessionStorage.setItem('lastLoginStep', '6');
  
  console.log('Step preserved for after reload');
};

// تابع کمکی برای نمایش وضعیت لاگین
window.showLoginStatus = function() {
  console.log('=== LOGIN STATUS ===');
  console.log('vosUserData:', window.vosUserData);
  console.log('isUserLoggedIn:', window.isUserLoggedIn());
  console.log('user_id:', window.vosUserData?.user_id);
  console.log('=== END LOGIN STATUS ===');
};

// تابع دیباگ برای نمایش وضعیت مراحل
window.debugSteps = function() {
  const allSteps = Array.from(document.querySelectorAll('.form--step'));
  const currentStep = document.querySelector('.form--step.current');

  console.log('=== DEBUG STEPS ===');
  console.log('Current step:', currentStep ? currentStep.dataset.step : 'none');
  console.log('Total steps:', allSteps.length);

  allSteps.forEach(step => {
    console.log(`Step ${step.dataset.step}:`, {
      current: step.classList.contains('current'),
      visible: step.style.display !== 'none',
      active: step.classList.contains('active')
    });
  });

  // بررسی وضعیت لاگین
  const loginStep = document.querySelector('.step-6');
  const isUserLoggedIn = window.vosUserData && window.vosUserData.user_id;

  console.log('Login status:', {
    step6Exists: !!loginStep,
    step6Active: loginStep?.classList.contains('active'),
    step6Visible: loginStep?.style.display !== 'none',
    isUserLoggedIn: isUserLoggedIn,
    vosUserData: window.vosUserData
  });

  console.log('=== END DEBUG ===');
};

// تابع کمکی برای بررسی وضعیت انتقال
window.checkNavigationState = function() {
  const currentStep = document.querySelector('.form--step.current');
  const allSteps = Array.from(document.querySelectorAll('.form--step'));
  
  console.log('=== NAVIGATION STATE ===');
  console.log('Current step:', currentStep ? currentStep.dataset.step : 'none');
  console.log('Total steps:', allSteps.length);
  
  // بررسی وجود مراحل مهم
  const step20 = document.querySelector('.form--step[data-step="20"]');
  const step6 = document.querySelector('.form--step[data-step="6"]');
  const step7 = document.querySelector('.form--step[data-step="7"]');
  
  console.log('Important steps:', {
    step20: !!step20,
    step6: !!step6,
    step7: !!step7
  });
  
  // بررسی وضعیت لاگین
  console.log('Login status:', {
    isUserLoggedIn: window.isUserLoggedIn(),
    vosUserData: window.vosUserData
  });
  
  console.log('=== END NAVIGATION STATE ===');
};

// تابع کمکی برای تنظیم وضعیت لاگین بعد از موفقیت
window.setLoginSuccess = function(userData) {
  console.log('=== SET LOGIN SUCCESS ===');
  console.log('User data:', userData);
  
  // تنظیم flag برای جلوگیری از ریست مرحله
  isLoginInProgress = true;
  console.log('Login flag set to prevent step reset');
  
  // تنظیم vosUserData
  if (userData && userData.user_id) {
    window.vosUserData = {
      user_id: userData.user_id,
      phone: userData.phone || null,
      address_name: null,
      address_id: null
    };
    
    console.log('vosUserData set successfully:', window.vosUserData);
    
    // بررسی مرحله فعلی
    const currentStep = document.querySelector('.form--step.current');
    if (currentStep) {
      const currentStepNumber = currentStep.dataset.step;
      console.log('Current step before navigation:', currentStepNumber);
      
      // حفظ مرحله فعلی
      window.preserveCurrentStep();
      
      console.log('Step preserved, navigating in 500ms...');
      
      // هدایت به مرحله بعدی با تاخیر بیشتر
      setTimeout(() => {
        console.log('Executing navigation...');
        window.navigateAfterLogin();
        
        // بعد از انتقال، flag را ریست کن
        setTimeout(() => {
          isLoginInProgress = false;
          console.log('Login flag reset');
        }, 1000);
      }, 500);
    } else {
      console.error('No current step found in setLoginSuccess');
      isLoginInProgress = false;
    }
  } else {
    console.error('Invalid user data provided:', userData);
    isLoginInProgress = false;
  }
  console.log('=== END SET LOGIN SUCCESS ===');
};

// تابع کمکی برای حفظ مرحله فعلی
window.preserveCurrentStep = function() {
  const currentStep = document.querySelector('.form--step.current');
  if (currentStep) {
    preservedStep = currentStep.dataset.step;
    console.log('Preserving current step:', preservedStep);
    return preservedStep;
  }
  return null;
};

// تابع کمکی برای بازیابی مرحله فعلی
window.restoreCurrentStep = function() {
  if (preservedStep) {
    console.log('Restoring preserved step:', preservedStep);
    const stepElement = document.querySelector(`.form--step[data-step="${preservedStep}"]`);
    if (stepElement) {
      // حذف کلاس current از تمام مراحل
      document.querySelectorAll('.form--step').forEach(step => {
        step.classList.remove('current');
      });
      
      // تنظیم مرحله ذخیره شده به عنوان فعلی
      stepElement.classList.add('current');
      
      // به‌روزرسانی main dataset
      if (main) {
        main.dataset.step = preservedStep;
        main.dataset.stepState = "def";
      }
      
      // اجرای توابع مرحله
      applyStepFuncs(parseInt(preservedStep));
      
      return true;
    }
  }
  return false;
};

// تابع دیباگ جامع برای بررسی وضعیت فرم
window.debugFormState = function() {
  console.log('=== COMPREHENSIVE FORM DEBUG ===');
  
  // بررسی مرحله فعلی
  const currentStep = document.querySelector('.form--step.current');
  console.log('Current step element:', currentStep);
  console.log('Current step number:', currentStep?.dataset.step);
  
  // بررسی تمام مراحل
  const allSteps = document.querySelectorAll('.form--step');
  console.log('Total steps found:', allSteps.length);
  
  allSteps.forEach((step, index) => {
    console.log(`Step ${index}:`, {
      stepNumber: step.dataset.step,
      isCurrent: step.classList.contains('current'),
      isVisible: step.style.display !== 'none',
      hasActive: step.classList.contains('active')
    });
  });
  
  // بررسی main dataset
  console.log('Main dataset:', {
    step: main?.dataset.step,
    stepState: main?.dataset.stepState
  });
  
  // بررسی preserved step
  console.log('Preserved step:', preservedStep);
  
  // بررسی vosUserData
  console.log('vosUserData:', window.vosUserData);
  console.log('isUserLoggedIn:', window.isUserLoggedIn());
  
  // بررسی مراحل مهم
  const step20 = document.querySelector('.form--step[data-step="20"]');
  const step6 = document.querySelector('.form--step[data-step="6"]');
  const step7 = document.querySelector('.form--step[data-step="7"]');
  
  console.log('Important steps:', {
    step20: !!step20,
    step6: !!step6,
    step7: !!step7
  });
  
  console.log('=== END COMPREHENSIVE FORM DEBUG ===');
};

// تابع کمکی برای بررسی وضعیت flag لاگین
window.checkLoginFlag = function() {
  console.log('=== LOGIN FLAG STATUS ===');
  console.log('isLoginInProgress:', isLoginInProgress);
  console.log('preservedStep:', preservedStep);
  console.log('=== END LOGIN FLAG STATUS ===');
};

