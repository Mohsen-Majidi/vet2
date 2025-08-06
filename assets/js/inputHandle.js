const dropdownCheckboxes = document.querySelectorAll(".checkbox-type-2");
const cost = document.querySelector(".form-step-nav .cost .price");
const totalStepPriceArr = [];

dropdownCheckboxes.forEach((dropdown, ddIndex) => {
  const btn = dropdown.querySelector(".label-btn");
  const masterCheckbox = dropdown.querySelector(".master-checkbox");
  const childInps = dropdown.querySelectorAll(".options input");
  const checkedCounter = dropdown.querySelector(".checked-counter");
  const totalPrice = dropdown.querySelector(".label-btn .price");

  // toggle dropdown
  btn.addEventListener("click", () => {
    const activeDD = document.querySelector(".checkbox-type-2.active");

    if (dropdown.classList.contains("active")) {
      dropdown.classList.remove("active");
    } else {
      activeDD && activeDD.classList.remove("active");
      dropdown.classList.add("active");
    }
  });

  // master checkbox click
  masterCheckbox.addEventListener("click", (e) => {
    childInps.forEach((inp) => {
      if (masterCheckbox.checked) {
        inp.checked = true;
      } else {
        inp.checked = false;
      }
    });
    inpChangeHandle();
  });

  // every checkbox change
  const inpChangeHandle = () => {
    let isSomeChecked = false;
    let checkedCount = 0;
    let totalCheckedPrice = 0;
    childInps.forEach((inp) => {
      if (inp.checked) {
        isSomeChecked = true;
        checkedCount++;

        const price = +inp.dataset.price;
        totalCheckedPrice += price;
      }
    });
    isSomeChecked
        ? (masterCheckbox.checked = true)
        : (masterCheckbox.checked = false);

    const hasCheckedClass = dropdown.classList.contains("checked");
    checkedCounter.textContent = checkedCount;
    totalCheckedPrice != 0
        ? (totalPrice.textContent = `
    ${totalCheckedPrice.toLocaleString("en-US")} تومان`)
        : (totalPrice.textContent = "200,000 تومان");

    totalStepPriceArr[ddIndex] = totalCheckedPrice;

    if (checkedCount != 0) {
      !hasCheckedClass && dropdown.classList.add("checked");
    } else {
      hasCheckedClass && dropdown.classList.remove("checked");
    }

    const total = totalStepPriceArr.reduce((tot, sNum) => tot + sNum, 0);
    cost.textContent = `${total.toLocaleString("en-US")} تومان`;

    disableButtonCan();
  };

  childInps.forEach((inp) => {
    inp.addEventListener("change", inpChangeHandle);
  });
});

// step 5 radio date handle

const dateContainers = document.querySelectorAll(
    ".form--step.step-5 .radio-type-2"
);
const timeContainers = document.querySelectorAll(
    ".form--step.step-5 .radio-type-3"
);

const chosenDateElem = document.querySelector(".form-step-nav .date .time");

const updateTimeAvailability = () => {
  const dateInp = document.querySelector(".date-options input:checked");
  const firstDateInp = document.querySelector(
      ".date-options .radio-type-2:first-child input"
  );
  const isToday = dateInp === firstDateInp;
  const now = new Date();

  timeContainers.forEach((container) => {
    const btn = container.querySelector("button");
    const inp = container.querySelector("input");

    if (!inp) return;

    let shouldDisable = false;

    if (isToday) {
      const [from] = inp.value.split("-");
      const [h, m] = from.split(":").map(Number);
      const start = new Date();
      start.setHours(h, m, 0, 0);
      const diffHours = (start - now) / (1000 * 60 * 60);
      if (diffHours < 3) shouldDisable = true;
    }

    inp.disabled = shouldDisable;
    btn.disabled = shouldDisable;

    if (shouldDisable && inp.checked) inp.checked = false;
  });
};

updateTimeAvailability();

document.querySelectorAll(".date-options input").forEach((inp) => {
  inp.addEventListener("change", updateTimeAvailability);
});


const setChosenDate = () => {
  const dateInp = document.querySelector(".date-options input:checked");
  const timeInp = document.querySelector(".time-options input:checked");

  if (!dateInp || !timeInp) {
    // مثلا بنویس انتخاب کنید یا متن پیش‌فرض بزنه
    chosenDateElem.textContent = "یک تاریخ و بازه زمانی انتخاب کنید";
    return;
  }

  const { week, monthfa, day } = dateInp.dataset;
  const timeRange = timeInp.value;
  const [from, to] = timeRange.split("-");
  const format = (time) =>
      time.split(":")[1] === "00" ? time.split(":")[0] : time;

  const formatedTimeString = `${week} ${day} ${monthfa} ساعت ${format(
      from
  )} تا ${format(to)}`;

  chosenDateElem.textContent = formatedTimeString;
};


setChosenDate();

dateContainers.forEach((item) => {
  const inp = item.querySelector("input");
  inp.checked && item.classList.add("checked");
  inp.addEventListener("change", setChosenDate);
});
timeContainers.forEach((item) => {
  const inp = item.querySelector("input");
  inp.addEventListener("change", setChosenDate);
});

// radio type 2

const rt2Containers = document.querySelectorAll(".radio-type-2");

rt2Containers.forEach((item) => {
  const input = item.querySelector("input");
  input.addEventListener("click", () => {
    if (!item.classList.contains("checked")) {
      const currentChecked = item.parentNode.querySelector(
          ".radio-type-2.checked"
      );
      currentChecked.classList.remove("checked");
      item.classList.add("checked");
    }
  });
});


// Dragging scroll in pc
const scrollContainer = document.querySelector(".dragging-scroller");
let isDown = false;
let startX;
let scrollLeft;

scrollContainer.addEventListener("mousedown", (e) => {
  isDown = true;
  startX = e.pageX - scrollContainer.offsetLeft;
  scrollLeft = scrollContainer.scrollLeft;
});

scrollContainer.addEventListener("mouseleave", () => {
  isDown = false;
});

scrollContainer.addEventListener("mouseup", () => {
  isDown = false;
});

scrollContainer.addEventListener("mousemove", (e) => {
  if (!isDown) return;
  e.preventDefault();
  const x = e.pageX - scrollContainer.offsetLeft;
  const walk = (startX - x) * 1.3;
  scrollContainer.scrollLeft = scrollLeft + walk;
});


