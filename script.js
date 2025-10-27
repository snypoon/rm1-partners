document.addEventListener("DOMContentLoaded", function () {
  const swiper = new Swiper(".slider__container", {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: true,
    loopAdditionalSlides: 2,
    breakpoints: {
      767: {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        loopAdditionalSlides: 2,
      },
    },
  });

  // Инициализация слайдера для блока efficiency
  const efficiencySwiper = new Swiper(".efficiency-swiper", {
    slidesPerView: 1,
    spaceBetween: 20,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
      758: {
        slidesPerView: 3,
        enabled: false,
      },
    },
  });

  if (window.innerWidth < 758) {
    const partnersSwiper = new Swiper(".partners-swiper", {
      slidesPerView: 1,
      spaceBetween: 20,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
    });
  }

  // Управление попапом
  const popup = document.getElementById("popup");
  const popupClose = document.getElementById("popup-close");
  const headerButton = document.querySelector(".header__button");

  // Функция открытия попапа
  function openPopup() {
    popup.classList.add("active");
    document.body.style.overflow = "hidden"; // Блокируем скролл страницы
  }

  // Функция закрытия попапа
  function closePopup() {
    popup.classList.remove("active");
    document.body.style.overflow = ""; // Восстанавливаем скролл страницы
  }

  // Обработчик клика на кнопку в хедере
  if (headerButton) {
    headerButton.addEventListener("click", openPopup);
  }

  // Обработчик клика на кнопку закрытия
  if (popupClose) {
    popupClose.addEventListener("click", closePopup);
  }

  // Обработчик клика на оверлей (фон попапа)
  if (popup) {
    popup.addEventListener("click", function (e) {
      if (e.target === popup || e.target.classList.contains("popup__overlay")) {
        closePopup();
      }
    });
  }

  // Обработчик нажатия клавиши Escape
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && popup.classList.contains("active")) {
      closePopup();
    }
  });

  // Функция для обработки отправки формы
  function handleFormSubmit(form, closePopupAfterSuccess = false) {
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.textContent;

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Блокируем кнопку на время отправки
      submitButton.disabled = true;
      submitButton.textContent = "Отправка...";

      // Собираем данные формы
      const formData = new FormData(form);

      // Отправка через fetch API
      fetch("send-email.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert(data.message || "Заявка успешно отправлена!");
            form.reset();
            // Закрываем попап после успешной отправки, если это форма в попапе
            if (closePopupAfterSuccess) {
              closePopup();
            }
          } else {
            alert(data.message || "Ошибка при отправке");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Произошла ошибка. Пожалуйста, попробуйте позже.");
        })
        .finally(() => {
          // Разблокируем кнопку
          submitButton.disabled = false;
          submitButton.textContent = originalButtonText;
        });
    });
  }

  // Обработка основной формы в секции contact
  const mainForm = document.querySelector(".contact form");
  if (mainForm) {
    handleFormSubmit(mainForm, false);
  }

  // Обработка формы в попапе
  const popupForm = document.querySelector("#popup form");
  if (popupForm) {
    handleFormSubmit(popupForm, true);
  }
});
