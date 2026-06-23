/* Keepsake — Wishlist. Saved product IDs live in localStorage, so the wishlist
   follows the visitor with no account and no server round-trip to read it. The
   saved-items page asks Till to render real product cards for the saved IDs. */
(function () {
	'use strict';

	var KEY = 'keepsake_items';
	var cfg = window.KEEPSAKE || {};

	function read() {
		try {
			var v = JSON.parse(localStorage.getItem(KEY));
			return Array.isArray(v) ? v.map(Number).filter(Boolean) : [];
		} catch (e) {
			return [];
		}
	}
	function write(ids) {
		try {
			localStorage.setItem(KEY, JSON.stringify(ids));
		} catch (e) {}
		updateCount();
		document.dispatchEvent(new CustomEvent('keepsake:changed', { detail: { ids: ids } }));
	}
	function has(id) { return read().indexOf(Number(id)) !== -1; }

	function toggle(id) {
		id = Number(id);
		var ids = read();
		var i = ids.indexOf(id);
		if (i === -1) {
			ids.push(id);
		} else {
			ids.splice(i, 1);
		}
		write(ids);
		return i === -1;
	}

	function updateCount() {
		var n = read().length;
		document.querySelectorAll('[data-keepsake-count]').forEach(function (el) {
			el.textContent = n;
			el.hidden = n === 0;
		});
	}

	function syncHearts(root) {
		(root || document).querySelectorAll('[data-keepsake]').forEach(function (btn) {
			var saved = has(btn.getAttribute('data-keepsake'));
			btn.classList.toggle('is-saved', saved);
			btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
			var label = btn.querySelector('.keepsake-heart__label');
			if (label) label.textContent = saved ? 'Saved' : 'Save';
		});
	}

	/* ---- saved-items page -------------------------------------------- */
	function renderList() {
		var wrap = document.querySelector('[data-keepsake-list]');
		if (!wrap) return;
		var grid = wrap.querySelector('[data-keepsake-grid]');
		var empty = wrap.querySelector('[data-keepsake-empty]');
		var ids = read();

		if (!ids.length) {
			if (grid) grid.innerHTML = '';
			if (empty) empty.style.display = '';
			return;
		}
		if (empty) empty.style.display = 'none';
		if (!cfg.ajax) return;

		var body = new URLSearchParams();
		body.set('action', 'keepsake_cards');
		body.set('nonce', cfg.nonce);
		ids.forEach(function (id) { body.append('ids[]', id); });

		fetch(cfg.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); }).then(function (res) {
			if (res && res.success && grid) {
				grid.innerHTML = res.data.html;
				syncHearts(grid);
			}
		}).catch(function () {});
	}

	/* ---- events ------------------------------------------------------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-keepsake]');
		if (!btn) return;
		e.preventDefault();
		var nowSaved = toggle(btn.getAttribute('data-keepsake'));
		btn.classList.toggle('is-saved', nowSaved);
		btn.setAttribute('aria-pressed', nowSaved ? 'true' : 'false');
		btn.classList.remove('is-pop');
		void btn.offsetWidth;
		btn.classList.add('is-pop');
		var label = btn.querySelector('.keepsake-heart__label');
		if (label) label.textContent = nowSaved ? 'Saved' : 'Save';

		// If we're on the wishlist page, removing should drop the card.
		if (!nowSaved && document.querySelector('[data-keepsake-list]')) {
			renderList();
		}
	});

	// Re-decorate cards that Till re-renders (e.g. after a cart update).
	document.addEventListener('till:updated', function () { syncHearts(); });
	document.addEventListener('keepsake:changed', function () { syncHearts(); });

	function init() {
		updateCount();
		syncHearts();
		renderList();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
