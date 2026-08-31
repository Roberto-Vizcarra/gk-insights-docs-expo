/**
 * GKI Docs Helper — Interactive Features
 * Loaded only on insights-expo category posts.
 *
 * Features:
 *  1. Auto-generated sticky Table of Contents
 *  2. Card search/filter
 *  3. Back-to-top button
 *  4. Reading progress bar
 *  5. Smooth scroll for anchor links
 */

(function () {
  'use strict';

  const page = document.querySelector('.gki-page');
  if (!page) return;

  /* ================================================================
     1. TABLE OF CONTENTS
     ================================================================ */
  function buildTOC() {
    const headings = page.querySelectorAll('.gki-section-title, .gki-subsection-title');
    if (headings.length < 3) return; // skip TOC for very short pages

    const nav = document.createElement('nav');
    nav.className = 'gki-toc';
    nav.setAttribute('aria-label', 'Table of contents');

    const title = document.createElement('div');
    title.className = 'gki-toc-title';
    title.textContent = 'On this page';
    nav.appendChild(title);

    const list = document.createElement('ul');
    list.className = 'gki-toc-list';

    headings.forEach(function (h, i) {
      // Ensure heading has an ID for anchor linking
      if (!h.id) {
        h.id = 'section-' + i;
      }

      const li = document.createElement('li');
      li.className = h.classList.contains('gki-subsection-title')
        ? 'gki-toc-item gki-toc-sub'
        : 'gki-toc-item';

      const a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent;
      a.className = 'gki-toc-link';

      li.appendChild(a);
      list.appendChild(li);
    });

    nav.appendChild(list);

    // Insert TOC after the card grid or intro, before first section
    const firstSection = page.querySelector('.gki-section');
    if (firstSection) {
      firstSection.parentNode.insertBefore(nav, firstSection);
    }

    // Highlight active section on scroll
    let ticking = false;
    const tocLinks = list.querySelectorAll('.gki-toc-link');

    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(function () {
          let current = '';
          headings.forEach(function (h) {
            if (h.getBoundingClientRect().top <= 100) {
              current = h.id;
            }
          });
          tocLinks.forEach(function (link) {
            link.classList.toggle(
              'gki-toc-active',
              link.getAttribute('href') === '#' + current
            );
          });
          ticking = false;
        });
        ticking = true;
      }
    });
  }

  /* ================================================================
     2. CARD SEARCH / FILTER
     ================================================================ */
  function buildSearch() {
    const grid = page.querySelector('.gki-card-grid');
    if (!grid) return;

    const cards = grid.querySelectorAll('.gki-card');
    if (cards.length < 4) return; // not worth filtering a few cards

    const wrapper = document.createElement('div');
    wrapper.className = 'gki-search-wrap';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'gki-search-input';
    input.placeholder = 'Filter pages…';
    input.setAttribute('aria-label', 'Filter related pages');

    wrapper.appendChild(input);

    // Insert before the grid
    grid.parentNode.insertBefore(wrapper, grid);

    input.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      cards.forEach(function (card) {
        var text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  /* ================================================================
     3. BACK-TO-TOP BUTTON
     ================================================================ */
  function buildBackToTop() {
    var btn = document.createElement('button');
    btn.className = 'gki-back-to-top';
    btn.setAttribute('aria-label', 'Back to top');
    btn.innerHTML = '&#8593;'; // up arrow
    btn.style.display = 'none';
    document.body.appendChild(btn);

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', function () {
      btn.style.display = window.scrollY > 400 ? '' : 'none';
    });
  }

  /* ================================================================
     4. READING PROGRESS BAR
     ================================================================ */
  function buildProgressBar() {
    var bar = document.createElement('div');
    bar.className = 'gki-progress-bar';
    document.body.appendChild(bar);

    window.addEventListener('scroll', function () {
      var scrollTop = window.scrollY;
      var docHeight = document.documentElement.scrollHeight - window.innerHeight;
      var progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      bar.style.width = progress + '%';
    });
  }

  /* ================================================================
     5. SMOOTH SCROLL FOR ANCHOR LINKS
     ================================================================ */
  function enableSmoothScroll() {
    page.addEventListener('click', function (e) {
      var link = e.target.closest('a[href^="#"]');
      if (!link) return;

      var target = document.getElementById(link.getAttribute('href').slice(1));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  /* ================================================================
     INJECT CSS FOR JS-POWERED FEATURES
     ================================================================ */
  function injectStyles() {
    var style = document.createElement('style');
    style.textContent = [
      /* TOC */
      '.gki-toc {',
      '  background: var(--gki-bg-subtle);',
      '  border: 1px solid var(--gki-border);',
      '  border-radius: var(--gki-radius);',
      '  padding: 1rem 1.25rem;',
      '  margin-bottom: 2rem;',
      '}',
      '.gki-toc-title {',
      '  font-size: 0.8rem;',
      '  font-weight: 600;',
      '  text-transform: uppercase;',
      '  letter-spacing: 0.05em;',
      '  color: var(--gki-text-muted);',
      '  margin-bottom: 0.5rem;',
      '}',
      '.gki-toc-list {',
      '  list-style: none;',
      '  padding: 0;',
      '  margin: 0;',
      '}',
      '.gki-toc-item {',
      '  margin: 0;',
      '}',
      '.gki-toc-sub {',
      '  padding-left: 1rem;',
      '}',
      '.gki-toc-link {',
      '  display: block;',
      '  padding: 0.25rem 0;',
      '  font-size: 0.88rem;',
      '  color: var(--gki-text-secondary);',
      '  text-decoration: none;',
      '  border-left: 2px solid transparent;',
      '  padding-left: 0.75rem;',
      '  transition: color 0.15s ease, border-color 0.15s ease;',
      '}',
      '.gki-toc-link:hover {',
      '  color: var(--gki-text);',
      '}',
      '.gki-toc-link.gki-toc-active {',
      '  color: var(--gki-purple);',
      '  border-left-color: var(--gki-purple);',
      '}',
      /* Search */
      '.gki-search-wrap {',
      '  margin-bottom: 0.75rem;',
      '}',
      '.gki-search-input {',
      '  width: 100%;',
      '  padding: 0.6rem 1rem;',
      '  background: var(--gki-bg-subtle);',
      '  border: 1px solid var(--gki-border);',
      '  border-radius: var(--gki-radius);',
      '  color: var(--gki-text);',
      '  font-size: 0.9rem;',
      '  font-family: var(--gki-font);',
      '  outline: none;',
      '  transition: border-color 0.15s ease;',
      '}',
      '.gki-search-input:focus {',
      '  border-color: var(--gki-purple);',
      '}',
      '.gki-search-input::placeholder {',
      '  color: var(--gki-text-muted);',
      '}',
      /* Back to top */
      '.gki-back-to-top {',
      '  position: fixed;',
      '  bottom: 2rem;',
      '  right: 2rem;',
      '  width: 40px;',
      '  height: 40px;',
      '  border-radius: 50%;',
      '  background: var(--gki-purple);',
      '  color: #fff;',
      '  border: none;',
      '  cursor: pointer;',
      '  font-size: 1.2rem;',
      '  line-height: 1;',
      '  box-shadow: var(--gki-shadow-md);',
      '  transition: opacity 0.2s ease, transform 0.2s ease;',
      '  z-index: 1000;',
      '}',
      '.gki-back-to-top:hover {',
      '  transform: translateY(-2px);',
      '  background: var(--gki-purple-hover);',
      '}',
      /* Progress bar */
      '.gki-progress-bar {',
      '  position: fixed;',
      '  top: 0;',
      '  left: 0;',
      '  height: 3px;',
      '  width: 0%;',
      '  background: linear-gradient(90deg, var(--gki-purple), var(--gki-blue));',
      '  z-index: 9999;',
      '  transition: width 0.1s linear;',
      '}'
    ].join('\n');
    document.head.appendChild(style);
  }

  /* ================================================================
     INIT
     ================================================================ */
  injectStyles();
  buildTOC();
  buildSearch();
  buildBackToTop();
  buildProgressBar();
  enableSmoothScroll();
})();
