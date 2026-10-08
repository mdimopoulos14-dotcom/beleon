/* Beleon: gallery picker for the tour/destination details box. */
(function () {
  'use strict';
  document.querySelectorAll('[data-bl-gallery]').forEach(function (box) {
    var input = box.querySelector('input[type=hidden]');
    var thumbs = box.querySelector('.bl-mb__thumbs');
    var frame;
    box.querySelector('[data-bl-gallery-pick]').addEventListener('click', function () {
      if (!window.wp || !wp.media) { return; }
      if (!frame) {
        frame = wp.media({ title: box.closest('.bl-mb__row').querySelector('label').textContent, multiple: 'add', library: { type: 'image' } });
        frame.on('open', function () {
          var sel = frame.state().get('selection');
          input.value.split(',').filter(Boolean).forEach(function (id) { sel.add(wp.media.attachment(id)); });
        });
        frame.on('select', function () {
          var items = frame.state().get('selection').toJSON();
          input.value = items.map(function (a) { return a.id; }).join(',');
          thumbs.innerHTML = '';
          items.forEach(function (a) {
            var img = document.createElement('img');
            img.src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail : a).url;
            thumbs.appendChild(img);
          });
        });
      }
      frame.open();
    });
    box.querySelector('[data-bl-gallery-clear]').addEventListener('click', function () {
      input.value = ''; thumbs.innerHTML = '';
    });
  });
})();
