(function () {
  'use strict';

  const dialogCloseButton = document.createElement('button');
  dialogCloseButton.className = 'close-button';
  dialogCloseButton.textContent = 'Close';

  const dialogContent = document.createElement('div');
  dialogContent.className = 'content';

  const dialog = document.createElement('dialog');
  dialog.appendChild(dialogCloseButton);
  dialog.appendChild(dialogContent);

  dialog.addEventListener('close', function () {
    dialogContent.innerHTML = '';
  });

  dialog.addEventListener('mousedown', function (event) {
    console.log(document.activeElement);

    if (document.activeElement === dialogCloseButton) {
      event.preventDefault();
      dialog.focus();
    } else {
      event.preventDefault();
      dialogCloseButton.focus();
    }
  });

  dialogCloseButton.addEventListener('click', function () {
    dialog.close();
  });

  document.body.appendChild(dialog);

  document.addEventListener('click', function (event) {
    const gallery = event.target.closest('.gallery');

    if (gallery) {
      event.preventDefault();

      if (!document.body.classList.contains('print-mode')) {
        const linkParentsOfImages = Array.from(gallery.querySelectorAll('img')).map(function (image) {
          return image.closest('a').href;
        });

        dialogContent.innerHTML = linkParentsOfImages.map(function (link) {
          return `<img src="${link}" alt="">`;
        }).join('');

        dialog.showModal();
      }
    }
  });
}());

(function () {
  const navigation = document.getElementById('navigation');
  const main = document.querySelector('main');

  if (navigation && main) {
    const navigationToggle = document.createElement('button');
    navigationToggle.id = 'toggle-print-button';
    navigationToggle.textContent = 'Print';

    const printToolbar = document.createElement('div');

    printToolbar.className = 'print-toolbar';
    printToolbar.innerHTML = `
      <div class="content">
        <p>Hide the parts of page your don't wish to print and press Print again when you're ready.</p>
        <div class="actions">
          <button id="print-button">Print</button>
          <button id="cancel-button">Cancel</button>
        </div>
      </div>
    `;

    navigation.closest('header').prepend(printToolbar);

    const printButton = document.getElementById('print-button');
    const cancelButton = document.getElementById('cancel-button');

    navigationToggle.addEventListener('click', function () {
      document.body.classList.toggle('print-mode');
      document.querySelectorAll('main > details').forEach(function (details) {
        details.dataset.nonPrintable = '';
      });
    });

    main.addEventListener('click', function (event) {
      if (document.body.classList.contains('print-mode')) {
        event.preventDefault();

        if (event.target !== main) {
          if (event.target.dataset.nonPrintable === '') {
            event.target.removeAttribute('data-non-printable');
          } else {
            event.target.dataset.nonPrintable = '';
          }
        }
      }
    });

    navigation.appendChild(navigationToggle);

    printButton.addEventListener('click', function () {
      window.print();
    });

    cancelButton.addEventListener('click', function () {
      document.body.classList.remove('print-mode');
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        document.body.classList.remove('print-mode');
      }
    });
  }
}());
