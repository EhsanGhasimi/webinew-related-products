(() => {
  'use strict';
  function init(root) {
    root.querySelectorAll('.wrp').forEach(slider => {
      if (slider.dataset.ready) return;
      slider.dataset.ready = '1';
      const cards = [...slider.querySelectorAll('.wrp-card')];
      const controls = slider.querySelector('.wrp-controls');
      if (!controls || cards.length < 2) return;
      let index = 0;
      let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      let hovered = false;
      let focused = false;
      let timer;
      const toggle = controls.querySelector('[data-autoplay]');
      const status = slider.querySelector('.wrp-status');
      const updateToggle = () => {
        toggle.textContent = paused ? 'پخش' : 'توقف';
        toggle.setAttribute('aria-label', paused ? 'شروع پخش خودکار' : 'توقف پخش خودکار');
        // Automatic slide changes must not repeatedly interrupt screen readers.
        status.setAttribute('aria-live', paused || hovered || focused ? 'polite' : 'off');
      };
      const schedule = () => {
        window.clearInterval(timer);
        timer = window.setInterval(() => {
          if (!paused && !hovered && !focused && !document.hidden && slider.isConnected) show(index + 1);
          if (!slider.isConnected) window.clearInterval(timer);
        }, 5000);
      };
      const show = next => {
        index = (next + cards.length) % cards.length;
        cards.forEach((card, n) => { card.hidden = n !== index; });
        slider.querySelector('.wrp-status').textContent = `${index + 1} / ${cards.length}`;
      };
      controls.hidden = false;
      controls.querySelectorAll('[data-step]').forEach(button => button.addEventListener('click', () => {
        show(index + Number(button.dataset.step));
        schedule();
      }));
      toggle.addEventListener('click', () => {
        paused = !paused;
        updateToggle();
        schedule();
      });
      slider.addEventListener('mouseenter', () => { hovered = true; updateToggle(); });
      slider.addEventListener('mouseleave', () => { hovered = false; updateToggle(); schedule(); });
      slider.addEventListener('focusin', () => { focused = true; updateToggle(); });
      slider.addEventListener('focusout', event => {
        focused = slider.contains(event.relatedTarget);
        updateToggle();
        schedule();
      });
      slider.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
          event.preventDefault();
          show(index + (event.key === 'ArrowLeft' ? 1 : -1));
          schedule();
        }
      });
      show(0);
      updateToggle();
      schedule();
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init(document));
  else init(document);
})();
