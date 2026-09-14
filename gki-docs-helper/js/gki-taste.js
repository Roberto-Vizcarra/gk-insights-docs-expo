/* ============================================================
   GKI Docs Helper — Taste exploration switch
   ------------------------------------------------------------
   Branch-only. Drives the three-way comparison between:

     stable  — no attribute, base gki-docs.css alone
     a       — html[data-taste="a"], craft overlay
     b       — html[data-taste="b"], craft + ambition overlays

   The attribute is set on <html> before first paint by the inline
   script in gki-docs-helper.php, so switching never flashes.
   Persisted in localStorage under 'gki-taste'; ?taste=a|b|stable in
   the URL wins for one visit and is then remembered.

   To retire this exploration: delete this file, css/taste-a.css,
   css/taste-b.css, the enqueue block, the wp_head init, and the
   .gki-taste-switch markup in templates/single-gki.php.
   ============================================================ */
(function () {
  'use strict';

  var STORAGE_KEY = 'gki-taste';
  var VARIANTS = ['stable', 'a', 'b'];

  function normalise(value) {
    return VARIANTS.indexOf(value) !== -1 ? value : 'stable';
  }

  function current() {
    var attr = document.documentElement.getAttribute('data-taste');
    return normalise(attr || 'stable');
  }

  function apply(variant) {
    variant = normalise(variant);

    if (variant === 'stable') {
      document.documentElement.removeAttribute('data-taste');
    } else {
      document.documentElement.setAttribute('data-taste', variant);
    }

    try {
      localStorage.setItem(STORAGE_KEY, variant);
    } catch (e) {}

    syncButtons(variant);

    // Pass B is the only variant with entry motion. Switching into it
    // after load means nothing has been observed yet, so run the setup.
    // Switching away leaves the elements revealed, which is correct —
    // they are already on screen.
    if (variant === 'b') {
      initReveal();
    }
  }

  function syncButtons(variant) {
    var buttons = document.querySelectorAll('.gki-taste-option');
    for (var i = 0; i < buttons.length; i++) {
      var isActive = buttons[i].getAttribute('data-taste-value') === variant;
      buttons[i].classList.toggle('gki-taste-option--active', isActive);
      buttons[i].setAttribute('aria-pressed', isActive ? 'true' : 'false');
    }
  }

  /* ---------- Entry reveal (Pass B only) --------------------
     IntersectionObserver, never a scroll listener — a scroll handler
     on a 26-card index page reflows continuously and drops frames on
     mobile.

     Only cards and section blocks are eligible. Body prose is
     deliberately excluded: animating text a reader is already
     scrolling toward makes the page feel slower, not richer.
     ---------------------------------------------------------- */
  var revealObserver = null;

  function initReveal() {
    if (!('IntersectionObserver' in window)) {
      return;
    }

    var reduceMotion = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) {
      return;
    }

    var targets = document.querySelectorAll(
      '.gki-card-grid > .gki-card, .gki-section, .gki-related, .gki-below-cards'
    );
    if (!targets.length) {
      return;
    }

    if (!revealObserver) {
      revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          var el = entry.target;
          var delay = parseInt(el.getAttribute('data-reveal-delay'), 10) || 0;

          window.setTimeout(function () {
            el.classList.add('gki-revealed');
            // Release the compositor layer once the transition is done.
            window.setTimeout(function () {
              el.classList.add('gki-reveal-done');
            }, 400);
          }, delay);

          revealObserver.unobserve(el);
        });
      }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.05
      });
    }

    for (var i = 0; i < targets.length; i++) {
      var el = targets[i];
      if (el.classList.contains('gki-reveal')) {
        continue;
      }

      el.classList.add('gki-reveal');

      // Stagger only within a card row, capped so a long grid never
      // leaves the last card waiting. 40ms x 3 columns max.
      if (el.classList.contains('gki-card')) {
        var siblings = Array.prototype.indexOf.call(el.parentNode.children, el);
        el.setAttribute('data-reveal-delay', String((siblings % 3) * 40));
      }

      // Anything already in view on load reveals immediately rather
      // than waiting for a scroll that may never come.
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.classList.add('gki-revealed', 'gki-reveal-done');
        continue;
      }

      revealObserver.observe(el);
    }
  }

  /* ---------- Switch UI ------------------------------------- */
  function initSwitch() {
    var wrap = document.querySelector('.gki-taste-switch');
    if (!wrap) {
      return;
    }

    wrap.addEventListener('click', function (event) {
      var button = event.target.closest('.gki-taste-option');
      if (!button) {
        return;
      }
      event.preventDefault();
      apply(button.getAttribute('data-taste-value'));
    });

    // Arrow-key navigation across the segmented control.
    wrap.addEventListener('keydown', function (event) {
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
        return;
      }
      var index = VARIANTS.indexOf(current());
      var next = event.key === 'ArrowRight'
        ? (index + 1) % VARIANTS.length
        : (index - 1 + VARIANTS.length) % VARIANTS.length;
      event.preventDefault();
      apply(VARIANTS[next]);

      var target = wrap.querySelector('[data-taste-value="' + VARIANTS[next] + '"]');
      if (target) {
        target.focus();
      }
    });

    syncButtons(current());
  }

  function init() {
    initSwitch();
    if (current() === 'b') {
      initReveal();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Exposed so the variant can be flipped from the console while
  // comparing: gkiTaste('b')
  window.gkiTaste = apply;
})();
