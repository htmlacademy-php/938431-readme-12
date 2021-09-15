'use strict';

'use script';

(function () {
  var ESC_KEYCODE = 27;

  window.util = {
    isEscEvent: function (evt, cb) {
      if (evt.keyCode === ESC_KEYCODE) {
        cb();
      }
    },

    getScrollbarWidth: function () {
      return window.innerWidth - document.documentElement.clientWidth;
    }
  }
})();
'use script';

(function () {
  var activeModal = document.querySelector('.modal--active');
  var modal = document.querySelector('.modal');
  var modalAdding = document.querySelector('.modal--adding');
  var addingPostSubmit = document.querySelector('.adding-post__submit');
  var scrollbarWidth = window.util.getScrollbarWidth() + 'px';
  var pageMainSection = document.querySelector('.page__main-section');
  var footerWrapper = document.querySelector('.footer__wrapper');

  var showModal = function (openButton, modal) {
    var closeButton = modal.querySelector('.modal__close-button');

    var onPopupEscPress = function (evt) {
      window.util.isEscEvent(evt, closeModal);
    };

    var closeModal = function (evt) {
      modal.classList.remove('modal--active');
      activeModal = false;
      document.removeEventListener('keydown', onPopupEscPress);
      document.documentElement.style.overflowY = 'auto';
      pageMainSection.style.paddingRight = '0';
      footerWrapper.style.paddingRight = '0';
    }

    var openModal = function (evt) {
      if (activeModal) {
        activeModal.classList.remove('modal--active');
      }

      modal.classList.add('modal--active');
      activeModal = modal;
      document.documentElement.style.overflowY = 'hidden';
      pageMainSection.style.paddingRight = scrollbarWidth;
      footerWrapper.style.paddingRight = scrollbarWidth;
      closeButton.focus();

      closeButton.addEventListener('click', function (evt) {
        evt.preventDefault();
        closeModal();
      });

      modal.addEventListener('click', function (evt) {
        if (evt.target === modal) {
          closeModal();
        }
      })

      document.addEventListener('keydown', onPopupEscPress);
    }

    openButton.addEventListener('click', function (evt) {
      openModal();
    });
  }

  // if (modal) {
  //   showModal(addingPostSubmit, modalAdding);
  // }
})();

(function() {
  var fileInputContainer = document.querySelector('.form__input-file-button--photo');
  var fileInput = fileInputContainer.querySelector('#file');
  var showFileName = function(name) {
    fileInputContainer.querySelector('span').textContent = name;
  }
  if (fileInput) {
    fileInput.addEventListener('change', (evt) => showFileName(evt.target.value));
  }
})();
