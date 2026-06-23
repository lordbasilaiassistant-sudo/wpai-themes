/* Till — Commerce. Cart interactions: AJAX add-to-cart, the slide-in drawer,
   line quantity steppers, and a small confirmation toast. Progressive
   enhancement — the cart and checkout pages work without JS; this just makes
   the experience feel instant. */
(function () {
	'use strict';

	if (typeof window.TILL === 'undefined') {
		return;
	}

	var cfg = window.TILL;
	var drawer = document.getElementById('till-drawer');
	var toastEl = document.querySelector('[data-till-toast]');
	var lastFocus = null;

	/* ---- helpers ------------------------------------------------------ */
	function post(action, data) {
		var body = new URLSearchParams();
		body.set('action', action);
		body.set('nonce', cfg.nonce);
		Object.keys(data || {}).forEach(function (k) {
			body.set(k, data[k]);
		});
		return fetch(cfg.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) {
			return r.json();
		});
	}

	function setCount(n) {
		document.querySelectorAll('[data-till-count]').forEach(function (el) {
			el.textContent = n;
			el.classList.toggle('is-filled', n > 0);
			el.classList.remove('is-bump');
			// Force reflow so the animation re-triggers.
			void el.offsetWidth;
			el.classList.add('is-bump');
		});
	}

	function refresh(payload) {
		if (!payload) return;
		if (typeof payload.count !== 'undefined') setCount(payload.count);

		var drawerBody = document.querySelector('[data-till-cart-drawer]');
		if (drawerBody && payload.drawer) drawerBody.innerHTML = payload.drawer;

		var cartPage = document.querySelector('[data-till-cart-page]');
		if (cartPage && payload.page) cartPage.innerHTML = payload.page;

		// Let other plugins (e.g. wishlist) re-decorate refreshed markup.
		document.dispatchEvent(new CustomEvent('till:updated', { detail: payload }));
	}

	function toast(msg) {
		if (!toastEl || !msg) return;
		toastEl.textContent = msg;
		toastEl.hidden = false;
		void toastEl.offsetWidth;
		toastEl.classList.add('is-shown');
		clearTimeout(toastEl._t);
		toastEl._t = setTimeout(function () {
			toastEl.classList.remove('is-shown');
		}, 2600);
	}

	/* ---- drawer ------------------------------------------------------- */
	function openDrawer() {
		if (!drawer) return;
		lastFocus = document.activeElement;
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		document.documentElement.style.overflow = 'hidden';
		var close = drawer.querySelector('.till-drawer__close');
		if (close) close.focus();
	}
	function closeDrawer() {
		if (!drawer) return;
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		document.documentElement.style.overflow = '';
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}

	/* ---- delegated clicks --------------------------------------------- */
	document.addEventListener('click', function (e) {
		var openBtn = e.target.closest('[data-till-open-cart]');
		if (openBtn) {
			e.preventDefault();
			openDrawer();
			return;
		}
		if (e.target.closest('[data-till-close-cart]')) {
			e.preventDefault();
			closeDrawer();
			return;
		}

		// Add to cart.
		var add = e.target.closest('[data-till-add]');
		if (add) {
			e.preventDefault();
			var id = add.getAttribute('data-till-add');
			var qty = 1;
			if (add.hasAttribute('data-till-qty-source')) {
				var input = document.querySelector('.till-qty__input');
				if (input) qty = Math.max(1, parseInt(input.value, 10) || 1);
			}
			add.classList.add('is-loading');
			post('till_add', { id: id, qty: qty }).then(function (res) {
				add.classList.remove('is-loading');
				if (res && res.success) {
					refresh(res.data);
					add.classList.add('is-done');
					setTimeout(function () { add.classList.remove('is-done'); }, 1100);
					openDrawer();
				} else {
					toast((res && res.data && res.data.message) || 'Could not add to cart.');
				}
			}).catch(function () {
				add.classList.remove('is-loading');
				toast('Network error — please try again.');
			});
			return;
		}

		// Line quantity +/-.
		var step = e.target.closest('[data-till-line-qty]');
		if (step) {
			e.preventDefault();
			var lid = step.getAttribute('data-till-line-qty');
			var delta = parseInt(step.getAttribute('data-till-delta'), 10) || 0;
			var valEl = step.parentNode.querySelector('.till-qty__val');
			var current = valEl ? parseInt(valEl.textContent, 10) || 0 : 0;
			updateLine(lid, current + delta);
			return;
		}

		// Remove a line.
		var rm = e.target.closest('[data-till-remove]');
		if (rm) {
			e.preventDefault();
			updateLine(rm.getAttribute('data-till-remove'), 0);
			return;
		}

		// Quantity stepper on the single product page.
		var qbtn = e.target.closest('[data-till-qty]');
		if (qbtn) {
			e.preventDefault();
			var box = qbtn.closest('.till-qty');
			var field = box ? box.querySelector('.till-qty__input') : null;
			if (field) {
				var n = Math.max(1, (parseInt(field.value, 10) || 1) + (parseInt(qbtn.getAttribute('data-till-qty'), 10) || 0));
				field.value = n;
			}
		}
	});

	function updateLine(id, qty) {
		post('till_update', { id: id, qty: qty }).then(function (res) {
			if (res && res.success) refresh(res.data);
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
			closeDrawer();
		}
	});
})();
