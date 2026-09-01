/**
 * GKI Docs Helper — Interactive Features (v1.8.0)
 * Loaded only on insights-expo category posts.
 *
 * Features:
 *  1. Auto-generated Table of Contents (sidebar or inline)
 *  2. Card search/filter (index pages)
 *  3. Back-to-top button
 *  4. Reading progress bar
 *  5. Smooth scroll for anchor links
 *  6. Image lightbox
 *  7. Collapsible nav (sections + sub-groups)
 *  8. Site-wide search
 */

(function () {
  'use strict';

  var page = document.querySelector('.gki-page') || document.querySelector('.gki-content-main');
  if (!page) return;

  var isIndexPage = document.body.classList.contains('gki-index-page');

  /* ================================================================
     1. TABLE OF CONTENTS
     ================================================================ */
  function buildTOC() {
    var customHeadings = page.querySelectorAll('.gki-section-title, .gki-subsection-title');
    var tocHeadings;

    if (customHeadings.length >= 3) {
      tocHeadings = Array.prototype.slice.call(customHeadings);
    } else {
      var all = page.querySelectorAll('h2, h3');
      tocHeadings = [];
      for (var i = 0; i < all.length; i++) {
        var h = all[i];
        if (h.closest('.exploration-notes')) continue;
        if (h.classList.contains('gki-page-title')) continue;
        if (h.classList.contains('gki-related-title')) continue;
        tocHeadings.push(h);
      }
    }

    if (tocHeadings.length < 3) return;

    var sidebarToc = document.getElementById('gki-sidebar-toc');

    var nav = document.createElement('nav');
    nav.className = 'gki-toc';
    nav.setAttribute('aria-label', 'Table of contents');

    var title = document.createElement('div');
    title.className = 'gki-toc-title';
    title.textContent = 'On this page';
    nav.appendChild(title);

    var list = document.createElement('ul');
    list.className = 'gki-toc-list';

    tocHeadings.forEach(function (h, idx) {
      if (!h.id) {
        h.id = 'section-' + idx;
      }
      var li = document.createElement('li');
      var isSub = h.classList.contains('gki-subsection-title') || h.tagName === 'H3';
      li.className = isSub ? 'gki-toc-item gki-toc-sub' : 'gki-toc-item';

      var a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent;
      a.className = 'gki-toc-link';
      li.appendChild(a);
      list.appendChild(li);
    });

    nav.appendChild(list);

    if (sidebarToc) {
      sidebarToc.appendChild(nav);
    } else {
      var firstSection = page.querySelector('.gki-section');
      if (firstSection) {
        firstSection.parentNode.insertBefore(nav, firstSection);
      }
    }

    // Scroll spy
    var ticking = false;
    var tocLinks = list.querySelectorAll('.gki-toc-link');

    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(function () {
          var current = '';
          tocHeadings.forEach(function (h) {
            if (h.getBoundingClientRect().top <= 120) {
              current = h.id;
            }
          });
          tocLinks.forEach(function (link) {
            var isActive = link.getAttribute('href') === '#' + current;
            link.classList.toggle('gki-toc-active', isActive);
            if (isActive && sidebarToc) {
              var linkRect = link.getBoundingClientRect();
              var sidebarRect = sidebarToc.getBoundingClientRect();
              if (linkRect.top < sidebarRect.top || linkRect.bottom > sidebarRect.bottom) {
                link.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
              }
            }
          });
          ticking = false;
        });
        ticking = true;
      }
    });
  }

  /* ================================================================
     2. CARD SEARCH / FILTER (index pages only)
     ================================================================ */
  function buildCardFilter() {
    var grid = page.querySelector('.gki-card-grid');
    if (!grid) return;

    var cards = grid.querySelectorAll('.gki-card');
    if (cards.length < 2) return;

    var wrapper = document.createElement('div');
    wrapper.className = 'gki-search-wrap';

    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'gki-search-input';
    input.placeholder = 'Filter pages…';
    input.setAttribute('aria-label', 'Filter related pages');

    wrapper.appendChild(input);
    grid.parentNode.insertBefore(wrapper, grid);

    input.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      var visibleCount = 0;
      cards.forEach(function (card) {
        var text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        var match = !q || text.indexOf(q) !== -1;
        card.classList.toggle('gki-hidden', !match);
        if (match) visibleCount++;
      });
      var noResults = grid.querySelector('.gki-no-results');
      if (visibleCount === 0 && q) {
        if (!noResults) {
          noResults = document.createElement('div');
          noResults.className = 'gki-no-results';
          noResults.textContent = 'No pages match “' + q + '”';
          grid.appendChild(noResults);
        }
      } else if (noResults) {
        noResults.parentNode.removeChild(noResults);
      }
    });
  }

  /* ================================================================
     3. BACK-TO-TOP BUTTON
     ================================================================ */
  function buildBackToTop() {
    var btn = document.createElement('button');
    btn.className = 'gki-back-to-top';
    btn.setAttribute('aria-label', 'Back to top');
    btn.innerHTML = '&#8593;';
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
    document.addEventListener('click', function (e) {
      var link = e.target.closest('a[href^="#"]');
      if (!link) return;
      var target = document.getElementById(link.getAttribute('href').slice(1));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (history.pushState) {
          history.pushState(null, null, link.getAttribute('href'));
        }
      }
    });
  }

  /* ================================================================
     6. IMAGE LIGHTBOX
     ================================================================ */
  function buildLightbox() {
    var imgs = page.querySelectorAll('img');
    if (!imgs.length) return;

    for (var i = 0; i < imgs.length; i++) {
      (function (img) {
        img.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          var overlay = document.createElement('div');
          overlay.className = 'gki-lightbox';

          var clone = document.createElement('img');
          clone.src = img.src;
          clone.alt = img.alt;
          overlay.appendChild(clone);

          function close() {
            overlay.style.opacity = '0';
            setTimeout(function () {
              if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 200);
          }

          overlay.addEventListener('click', close);
          function onKey(evt) {
            if (evt.key === 'Escape') {
              close();
              document.removeEventListener('keydown', onKey);
            }
          }
          document.addEventListener('keydown', onKey);
          document.body.appendChild(overlay);
        });
      })(imgs[i]);
    }
  }

  /* ================================================================
     7. COLLAPSIBLE NAV
     Sections and sub-groups collapse/expand on click. Active
     section is auto-expanded. Clicking navigates AND toggles.
     ================================================================ */
  function buildCollapsibleNav() {
    var sections = document.querySelectorAll('.gki-nav-section');
    sections.forEach(function (section) {
      var link = section.querySelector(':scope > .gki-nav-section-link');
      if (!link) return;

      link.addEventListener('click', function (e) {
        var isOpen = section.classList.contains('gki-nav-section--open');
        // If already on this section's page, just toggle without navigating
        if (link.hasAttribute('aria-current')) {
          e.preventDefault();
          section.classList.toggle('gki-nav-section--open');
          return;
        }
        // Otherwise toggle open (navigation will happen naturally)
        section.classList.toggle('gki-nav-section--open');
      });
    });

    // Sub-groups (e.g. metric families within Metrics section)
    var subgroups = document.querySelectorAll('.gki-nav-subgroup');
    subgroups.forEach(function (sg) {
      var link = sg.querySelector(':scope > .gki-nav-subgroup-link');
      if (!link) return;

      link.addEventListener('click', function (e) {
        if (link.hasAttribute('aria-current')) {
          e.preventDefault();
          sg.classList.toggle('gki-nav-subgroup--open');
          return;
        }
        sg.classList.toggle('gki-nav-subgroup--open');
      });
    });
  }

  /* ================================================================
     8. SITE-WIDE SEARCH
     Uses gkiSearchData (localized from PHP) to search across all
     pages. Renders results in a dropdown below the search input.
     ================================================================ */
  function buildSiteSearch() {
    var searchInput = document.querySelector('.gki-site-search-input');
    var resultsContainer = document.querySelector('.gki-site-search-results');
    if (!searchInput || !resultsContainer) return;

    var pages = (window.gkiSearchData && window.gkiSearchData.pages) || [];
    if (!pages.length) return;

    var debounceTimer = null;

    searchInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      var q = this.value.toLowerCase().trim();

      if (!q || q.length < 2) {
        resultsContainer.hidden = true;
        resultsContainer.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(function () {
        var matches = [];
        pages.forEach(function (p) {
          var searchText = (p.title + ' ' + p.desc + ' ' + p.excerpt).toLowerCase();
          var score = 0;
          // Title match is highest priority
          if (p.title.toLowerCase().indexOf(q) !== -1) score += 10;
          // Description match
          if (p.desc.toLowerCase().indexOf(q) !== -1) score += 5;
          // Excerpt match
          if (p.excerpt.toLowerCase().indexOf(q) !== -1) score += 1;

          if (score > 0) {
            matches.push({ page: p, score: score });
          }
        });

        // Sort by score descending, limit to 8 results
        matches.sort(function (a, b) { return b.score - a.score; });
        matches = matches.slice(0, 8);

        if (matches.length === 0) {
          resultsContainer.innerHTML = '<div class="gki-search-no-results">No results for “' + q + '”</div>';
          resultsContainer.hidden = false;
          return;
        }

        var html = '';
        matches.forEach(function (m) {
          var p = m.page;
          var catLabel = p.cat ? p.cat.replace(/-/g, ' ') : '';
          html += '<a href="' + p.url + '" class="gki-search-result">';
          html += '<div class="gki-search-result-title">' + escapeHtml(p.title) + '</div>';
          if (p.desc) {
            html += '<div class="gki-search-result-desc">' + escapeHtml(p.desc) + '</div>';
          }
          if (catLabel) {
            html += '<div class="gki-search-result-cat">' + escapeHtml(catLabel) + '</div>';
          }
          html += '</a>';
        });

        resultsContainer.innerHTML = html;
        resultsContainer.hidden = false;
      }, 150);
    });

    // Close results on click outside
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.gki-site-search')) {
        resultsContainer.hidden = true;
      }
    });

    // Close on Escape
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        resultsContainer.hidden = true;
        searchInput.blur();
      }
    });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  /* ================================================================
     INJECT CSS FOR JS-POWERED FEATURES
     ================================================================ */
  function injectStyles() {
    var style = document.createElement('style');
    style.textContent = [
      /* Inline TOC (fallback when no sidebar template) */
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
      '.gki-toc-item { margin: 0; }',
      '.gki-toc-sub { padding-left: 1rem; }',
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
      '.gki-toc-link:hover { color: var(--gki-text); }',
      '.gki-toc-link.gki-toc-active {',
      '  color: var(--gki-purple);',
      '  border-left-color: var(--gki-purple);',
      '}',
      /* Card filter */
      '.gki-search-wrap { margin-bottom: 0.75rem; }',
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
      '.gki-search-input:focus { border-color: var(--gki-purple); }',
      '.gki-search-input::placeholder { color: var(--gki-text-muted); }',
      '.gki-no-results {',
      '  grid-column: 1 / -1;',
      '  text-align: center;',
      '  padding: 2rem 1rem;',
      '  color: var(--gki-text-muted);',
      '  font-size: 0.9rem;',
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
  buildCollapsibleNav();
  buildSiteSearch();
  if (!isIndexPage) {
    buildTOC();
    buildProgressBar();
  }
  buildCardFilter();
  buildBackToTop();
  enableSmoothScroll();
  buildLightbox();
})();
