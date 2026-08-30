/* DivumWX draggable cards + persistence (v1.1)
   - Auto-detects all elements with class ".divumwxbox"
   - Groups by their immediate parent container (works with grid/flex/flow layouts)
   - Delegated drag/drop so dynamically added cards also work
   - Persists order per container and per page (location.pathname)
   - No dependencies
*/
(function () {
  'use strict';

  var CARD_SELECTOR = '.divumwxbox';
  var KEY_PREFIX = 'divumwx.cardOrder:';

  // Helpers
  function throttle(fn, wait) {
    var t = 0, queued = false, lastArgs, lastThis;
    return function () {
      lastArgs = arguments; lastThis = this;
      if (!queued) {
        queued = true;
        var now = Date.now();
        var delay = Math.max(0, wait - (now - t));
        setTimeout(function () {
          t = Date.now(); queued = false;
          fn.apply(lastThis, lastArgs);
        }, delay);
      }
    };
  }

  function domPath(el) {
    var parts = [];
    while (el && el.nodeType === 1 && el !== document.body) {
      var name = el.tagName.toLowerCase();
      var id = el.id ? ('#' + el.id) : '';
      var cls = el.className && typeof el.className === 'string'
        ? '.' + el.className.trim().replace(/\s+/g, '.')
        : '';
      parts.unshift(name + id + cls);
      el = el.parentElement;
    }
    return parts.join(' > ');
  }

  function stableIdForCard(el, idx) {
    if (el.id) return el.id;
    var t = '';
    var trySel = ['[data-title]', '.title', '.heading1', 'h1', 'h2', 'h3', '[titlespan]'];
    for (var i = 0; i < trySel.length; i++) {
      var n = el.querySelector(trySel[i]);
      if (n && n.textContent) { t = n.textContent.trim(); break; }
    }
    if (!t) t = Array.from(el.classList).join('-');
    t = t.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '').slice(0, 40);
    return (t || 'card') + '-' + idx;
  }

  function storageKeyForContainer(container) {
    return KEY_PREFIX + location.pathname + '::' + domPath(container);
  }

  function loadOrder(storageKey) {
    try {
      var raw = localStorage.getItem(storageKey);
      if (!raw) return null;
      var data = JSON.parse(raw);
      if (!data || !Array.isArray(data.order)) return null;
      return data.order;
    } catch (e) {
      console.warn('[DivumWX drag] Failed to parse saved order', e);
      return null;
    }
  }

  function saveOrder(storageKey, container) {
    var ids = Array.from(container.querySelectorAll(CARD_SELECTOR)).map(function (el) {
      return el.dataset.cardId;
    });
    try {
      localStorage.setItem(storageKey, JSON.stringify({ version: 1, order: ids }));
    } catch (e) {
      console.warn('[DivumWX drag] Failed to save order', e);
    }
  }
  var saveOrderThrottled = throttle(saveOrder, 150);

  function applyOrder(container, orderIds) {
    if (!orderIds) return;
    var nodes = Array.from(container.querySelectorAll(CARD_SELECTOR));
    var byId = new Map(nodes.map(function (n) { return [n.dataset.cardId, n]; }));
    orderIds.forEach(function (id) {
      var el = byId.get(id);
      if (el) container.appendChild(el);
    });
    // Append any newcomers
    nodes.forEach(function (n) {
      if (orderIds.indexOf(n.dataset.cardId) === -1) container.appendChild(n);
    });
  }

  function isInteractive(el) {
    return el && (
      el.closest('a, button, input, textarea, select, label, [role="button"], [role="link"]')
    );
  }

  function setupForContainer(container) {
    var storageKey = storageKeyForContainer(container);
    // Ensure all existing cards have ids + draggable
    Array.from(container.querySelectorAll(CARD_SELECTOR)).forEach(function (el, idx) {
      if (!el.dataset.cardId) el.dataset.cardId = stableIdForCard(el, idx);
      el.setAttribute('draggable', 'true');
      if (!el.style.cursor) el.style.cursor = 'move';
    });
    // Apply saved order
    applyOrder(container, loadOrder(storageKey));

    var dragging = null;

    // Delegate events
    container.addEventListener('dragstart', function (e) {
      var card = e.target.closest(CARD_SELECTOR);
      if (!card || !container.contains(card)) return;
      // Avoid starting drag when interacting with form controls/links inside
      if (isInteractive(e.target)) return;
      dragging = card;
      card.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', card.dataset.cardId || '');
      document.body.classList.add('divumwx-drag-active');
      // Optional custom ghost
      try {
        var g = card.cloneNode(true);
        g.style.position = 'absolute';
        g.style.top = '-99999px';
        g.style.left = '-99999px';
        g.style.opacity = '0.6';
        document.body.appendChild(g);
        e.dataTransfer.setDragImage(g, 20, 20);
        setTimeout(function(){ if (g && g.parentNode) g.parentNode.removeChild(g); }, 0);
      } catch (_) {}
    });

    container.addEventListener('dragend', function () {
      if (dragging) dragging.classList.remove('dragging');
      container.querySelectorAll(CARD_SELECTOR + '.drag-over').forEach(function (n) {
        n.classList.remove('drag-over');
      });
      dragging = null;
      document.body.classList.remove('divumwx-drag-active');
      saveOrderThrottled(storageKey, container);
    });

    container.addEventListener('dragover', function (e) {
      if (!dragging) return;
      e.preventDefault(); // allow drop
      var target = e.target.closest(CARD_SELECTOR);
      if (!target || target === dragging) return;

      target.classList.add('drag-over');
      var rect = target.getBoundingClientRect();
      var midY = rect.top + rect.height / 2;
      var midX = rect.left + rect.width / 2;

      // Heuristic that works for grid/flex: decide by whichever axis is larger
      var style = getComputedStyle(container);
      var isRow = style.display.includes('flex') && style.flexDirection.startsWith('row');

      var before;
      if (isRow) {
        before = e.clientX < midX;
      } else {
        // For grid or block, vertical feels more natural
        before = e.clientY < midY;
      }

      if (before) container.insertBefore(dragging, target);
      else container.insertBefore(dragging, target.nextSibling);
    });

    container.addEventListener('dragleave', function (e) {
      var card = e.target.closest(CARD_SELECTOR);
      if (card) card.classList.remove('drag-over');
    });

    container.addEventListener('drop', function (e) {
      if (!dragging) return;
      e.preventDefault();
      saveOrderThrottled(storageKey, container);
    });

    // Observe additions/removals and keep attributes in place
    var mo = new MutationObserver(function () {
      Array.from(container.querySelectorAll(CARD_SELECTOR)).forEach(function (el, idx) {
        if (!el.dataset.cardId) el.dataset.cardId = stableIdForCard(el, idx);
        if (!el.getAttribute('draggable')) el.setAttribute('draggable', 'true');
      });
    });
    mo.observe(container, { childList: true, subtree: true });
  }

  function boot() {
    var cards = Array.from(document.querySelectorAll(CARD_SELECTOR));
    if (!cards.length) {
      console.warn('[DivumWX drag] No elements matching ' + CARD_SELECTOR);
      return;
    }
    // Group by immediate parent
    var groups = new Map();
    cards.forEach(function (c) {
      if (!c.parentElement) return;
      var p = c.parentElement;
      if (!groups.has(p)) groups.set(p, true);
    });
    groups.forEach(function (_, container) { setupForContainer(container); });

    // Expose helpers
    window.DivumWXDrag = {
      clearAll: function () {
        groups.forEach(function (_, container) {
          localStorage.removeItem(storageKeyForContainer(container));
        });
        console.info('[DivumWX drag] Cleared saved orders. Reload to reset layout.');
      },
      debug: function () {
        console.table(Array.from(groups.keys()).map(function (c, i) {
          return {
            idx: i,
            container: c.tagName.toLowerCase() + (c.id ? '#' + c.id : ''),
            path: domPath(c),
            cards: c.querySelectorAll(CARD_SELECTOR).length,
            key: storageKeyForContainer(c)
          };
        }));
      }
    };
  }

  // Minimal style guard to reduce text selection while dragging
  var style = document.createElement('style');
  style.textContent = `
    body.divumwx-drag-active { user-select: none !important; }
    .divumwxbox.dragging { opacity: 0.6; }
    .divumwxbox.drag-over { outline: 2px dashed var(--col-21, #808080); outline-offset: -2px; }
  `;
  document.head.appendChild(style);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
