(() => {
  'use strict';

  // Calcula la base real del proyecto (ej. /mundo_de_esther/v4/public) desde main.js
  const currentScript = document.currentScript || document.querySelector('script[src*="/assets/js/main.js"]');
  let baseUrl = '';
  if (currentScript && currentScript.src) {
    const scriptPath = new URL(currentScript.src).pathname;
    baseUrl = scriptPath.replace(/\/assets\/js\/main\.js(?:\?.*)?$/, '');
  } else if (window.location.pathname.includes('/public')) {
    baseUrl = window.location.pathname.split('/public')[0] + '/public';
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  // Modo oscuro / claro
  const htmlEl = document.documentElement;
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  function applyTheme(theme) {
    htmlEl.setAttribute('data-theme', theme);
    localStorage.setItem('mde_theme', theme);
    if (themeIcon) {
      themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
  }

  applyTheme(localStorage.getItem('mde_theme') || 'light');
  themeToggle?.addEventListener('click', () => {
    applyTheme(htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });

  // Carrusel de destacados
  const slides = Array.from(document.querySelectorAll('.carousel-slide'));
  let activeIndex = 0;

  function showSlide(idx) {
    if (!slides.length) return;
    activeIndex = (idx + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === activeIndex));
  }

  document.getElementById('prevSlide')?.addEventListener('click', () => showSlide(activeIndex - 1));
  document.getElementById('nextSlide')?.addEventListener('click', () => showSlide(activeIndex + 1));
  if (slides.length > 1) {
    setInterval(() => showSlide(activeIndex + 1), 5000);
  }

  // Menú móvil
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mainNav = document.getElementById('mainNav');
  hamburgerBtn?.addEventListener('click', () => {
    if (!mainNav) return;
    const isOpen = mainNav.style.display === 'flex';
    mainNav.style.display = isOpen ? 'none' : 'flex';
    mainNav.style.flexDirection = 'column';
    mainNav.style.position = 'absolute';
    mainNav.style.top = '70px';
    mainNav.style.left = '0';
    mainNav.style.right = '0';
    mainNav.style.padding = '1rem';
    mainNav.style.background = 'var(--surface)';
    mainNav.style.borderBottom = '1px solid var(--border-soft)';
  });

  // Filtros AJAX en vivo (v3)
  const filterForm = document.getElementById('catalogFilterForm');
  const resultsGrid = document.getElementById('catalogResultsGrid');
  const countLabel = document.getElementById('catalogCount');
  let debounceTimer = null;

  async function runLiveFilter() {
    if (!filterForm || !resultsGrid) return;
    const params = new URLSearchParams(new FormData(filterForm));
    try {
      const res = await fetch(`${baseUrl}/api/productos/filtrar?${params.toString()}`);
      const data = await res.json();
      if (!data.ok) return;

      if (countLabel) {
        countLabel.innerHTML = `Mostrando <strong>${data.total}</strong> peluches`;
      }

      if (!data.productos.length) {
        resultsGrid.innerHTML = `<div class="auth-card" style="grid-column:1/-1;text-align:center;">
          <p>No encontramos peluches con esos filtros.</p>
        </div>`;
        return;
      }

      const favSet = new Set(data.favoritosIds || []);
      resultsGrid.innerHTML = data.productos.map((p) => {
        const precio = p.precio_oferta ? Number(p.precio_oferta) : Number(p.precio);
        const esFav = favSet.has(Number(p.id));
        const tagHtml = p.etiqueta && p.etiqueta !== 'Ninguna'
          ? `<span class="tag-badge tag-${p.etiqueta.toLowerCase().replace(/\s+/g, '-')}">${escapeHtml(p.etiqueta)}</span>`
          : '';
        const oldHtml = p.precio_oferta
          ? `<span class="price-old">S/ ${Number(p.precio).toFixed(2)}</span>`
          : '';

        return `
          <article class="product-card">
            <div class="product-card__media">
              ${tagHtml}
              <button type="button" class="fav-btn ${esFav ? 'is-fav' : ''}" data-product-id="${p.id}" aria-label="Favorito">
                <i class="fa-${esFav ? 'solid' : 'regular'} fa-heart"></i>
              </button>
              <a href="${baseUrl}/producto?slug=${encodeURIComponent(p.slug)}" class="plush-placeholder">
                <i class="fa-solid fa-paw"></i>
                <span>${escapeHtml(p.tamano)} • Ver detalle</span>
              </a>
            </div>
            <div class="product-card__body">
              <div class="product-meta">
                <span>${escapeHtml(p.categoria_nombre)}</span>
                <span>• ${escapeHtml(p.color)}</span>
                <span>• ★ ${Number(p.rating_promedio || 5).toFixed(1)}</span>
              </div>
              <h3 class="product-card__title">
                <a href="${baseUrl}/producto?slug=${encodeURIComponent(p.slug)}">${escapeHtml(p.nombre)}</a>
              </h3>
              <p class="product-card__desc">${escapeHtml(p.descripcion)}</p>
              <div class="product-card__footer">
                <div class="price-box">
                  <span class="price-current">S/ ${precio.toFixed(2)}</span>
                  ${oldHtml}
                </div>
                <span class="stock-badge">${p.stock_actual} und.</span>
              </div>
              <div style="display:flex;gap:0.5rem;margin-top:0.85rem;">
                <button type="button" class="btn btn-soft btn-sm add-to-cart-btn" data-product-id="${p.id}" style="flex:1;">
                  <i class="fa-solid fa-cart-plus"></i> Agregar
                </button>
                <a href="${baseUrl}/producto?slug=${encodeURIComponent(p.slug)}" class="btn btn-primary btn-sm" style="flex:1;">
                  <i class="fa-solid fa-eye"></i> Detalle
                </a>
              </div>
            </div>
          </article>`;
      }).join('');
    } catch (err) {
      console.error('Error filtrando catálogo:', err);
    }
  }

  if (filterForm) {
    filterForm.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(runLiveFilter, 260);
    });
    filterForm.addEventListener('change', runLiveFilter);
    document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
      filterForm.reset();
      runLiveFilter();
    });
  }

  // Carrito lateral deslizable (v4)
  const cartDrawer = document.getElementById('cartDrawer');
  const cartBackdrop = document.getElementById('cartBackdrop');
  const cartItemsContainer = document.getElementById('cartItemsContainer');
  const drawerSubtotal = document.getElementById('drawerSubtotal');

  function openCart() {
    cartDrawer?.classList.add('is-open');
    cartBackdrop?.classList.add('is-open');
  }

  function closeCart() {
    cartDrawer?.classList.remove('is-open');
    cartBackdrop?.classList.remove('is-open');
  }

  document.querySelectorAll('.open-cart-trigger').forEach((btn) => {
    btn.addEventListener('click', async () => {
      await refreshCart();
      openCart();
    });
  });
  document.getElementById('closeCartBtn')?.addEventListener('click', closeCart);
  cartBackdrop?.addEventListener('click', closeCart);

  function renderCartData(data) {
    document.querySelectorAll('.cart-count-badge').forEach((b) => {
      b.textContent = String(data.total_unidades ?? 0);
    });

    if (drawerSubtotal) {
      drawerSubtotal.textContent = `S/ ${Number(data.subtotal ?? 0).toFixed(2)}`;
    }

    if (!cartItemsContainer) return;
    const items = data.items || [];

    if (!items.length) {
      cartItemsContainer.innerHTML = `<p style="color:var(--text-muted);text-align:center;margin-top:2rem;">
        Tu carrito está vacío. ¡Elige un peluche tierno!
      </p>`;
      return;
    }

    cartItemsContainer.innerHTML = items.map((it) => `
      <div class="cart-item-card">
        <div class="cart-item-card__top">
          <div>
            <strong>${escapeHtml(it.nombre)}</strong>
            <small style="display:block;color:var(--text-muted);">${escapeHtml(it.tamano)} • S/ ${Number(it.precio_unitario).toFixed(2)}</small>
          </div>
          <button type="button" class="icon-btn icon-btn--sm cart-remove-btn" data-cart-id="${it.id}" title="Quitar">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
        ${it.dedicatoria ? `<small class="cart-note">Dedicatoria: "${escapeHtml(it.dedicatoria)}"</small>` : ''}
        <div class="cart-item-card__bottom">
          <div class="qty-stepper">
            <button type="button" class="qty-btn" data-cart-id="${it.id}" data-qty="${it.cantidad - 1}">-</button>
            <span>${it.cantidad}</span>
            <button type="button" class="qty-btn" data-cart-id="${it.id}" data-qty="${it.cantidad + 1}">+</button>
          </div>
          <strong>S/ ${Number(it.subtotal_item).toFixed(2)}</strong>
        </div>
      </div>
    `).join('');
  }

  async function refreshCart() {
    try {
      const res = await fetch(`${baseUrl}/api/carrito`);
      const data = await res.json();
      if (data.ok) renderCartData(data);
    } catch (e) {
      console.error('Error cargando carrito:', e);
    }
  }

  document.addEventListener('click', async (e) => {
    const favBtn = e.target.closest('.fav-btn, .fav-btn-inline');
    if (favBtn) {
      const productId = favBtn.dataset.productId;
      const body = new URLSearchParams({ producto_id: productId, csrf_token: csrfToken });
      const res = await fetch(`${baseUrl}/api/favoritos/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
      const data = await res.json();
      if (data.requireLogin) {
        window.location.href = `${baseUrl}/login`;
        return;
      }
      if (data.ok) {
        favBtn.classList.toggle('is-fav', data.favorito);
        const icon = favBtn.querySelector('i');
        if (icon) icon.className = `fa-${data.favorito ? 'solid' : 'regular'} fa-heart`;
      }
      return;
    }

    const addBtn = e.target.closest('.add-to-cart-btn');
    if (addBtn) {
      const prodId = addBtn.dataset.productId;
      const qtyInput = document.getElementById('detailQty');
      const wrapInput = document.getElementById('detailEnvoltura');
      const dedInput = document.getElementById('detailDedicatoria');

      const body = new URLSearchParams({
        csrf_token: csrfToken,
        producto_id: prodId,
        cantidad: qtyInput ? qtyInput.value : '1',
        envoltura_regalo: wrapInput && wrapInput.checked ? '1' : '0',
        dedicatoria: dedInput ? dedInput.value : ''
      });

      try {
        const res = await fetch(`${baseUrl}/api/carrito/agregar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body
        });
        const data = await res.json();
        if (!res.ok || !data.ok) {
          alert(data.message || 'No se pudo agregar al carrito.');
          return;
        }
        renderCartData(data);
        openCart();
      } catch (err) {
        console.error('Error al agregar al carrito:', err);
        alert('Error de conexión al agregar al carrito.');
      }
      return;
    }

    const qtyBtn = e.target.closest('.qty-btn');
    if (qtyBtn) {
      const body = new URLSearchParams({
        csrf_token: csrfToken,
        carrito_id: qtyBtn.dataset.cartId,
        cantidad: qtyBtn.dataset.qty
      });
      const res = await fetch(`${baseUrl}/api/carrito/cantidad`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
      const data = await res.json();
      if (data.ok) renderCartData(data);
      return;
    }

    const delBtn = e.target.closest('.cart-remove-btn');
    if (delBtn) {
      const body = new URLSearchParams({
        csrf_token: csrfToken,
        carrito_id: delBtn.dataset.cartId
      });
      const res = await fetch(`${baseUrl}/api/carrito/eliminar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
      const data = await res.json();
      if (data.ok) renderCartData(data);
    }
  });

  // Zoom en detalle de producto
  const zoomStage = document.getElementById('zoomStage');
  const zoomTarget = document.getElementById('zoomTarget');
  zoomStage?.addEventListener('mousemove', (e) => {
    if (!zoomTarget) return;
    const rect = zoomStage.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    zoomTarget.style.transformOrigin = `${x}% ${y}%`;
    zoomTarget.style.transform = 'scale(1.35)';
  });
  zoomStage?.addEventListener('mouseleave', () => {
    if (zoomTarget) zoomTarget.style.transform = 'scale(1)';
  });

  document.querySelectorAll('.thumb-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.thumb-btn').forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      const cap = document.getElementById('zoomCaption');
      if (cap) cap.textContent = btn.dataset.label || '';
    });
  });

  // Cálculo dinámico en Checkout + Cupón AJAX
  const checkoutForm = document.getElementById('checkoutForm');
  if (checkoutForm) {
    const subtotalBase = Number(document.getElementById('sumSubtotal')?.dataset.val || 0);
    const wrapCheck = document.getElementById('envoltura_regalo');
    const entregaSel = document.getElementById('tipo_entrega');
    const dirGroup = document.getElementById('direccionGroup');
    const dirInput = document.getElementById('direccion_entrega');
    const sumEnvoltura = document.getElementById('sumEnvoltura');
    const sumEnvio = document.getElementById('sumEnvio');
    const sumDescuento = document.getElementById('sumDescuento');
    const sumTotal = document.getElementById('sumTotal');
    let descuentoActual = 0;

    function recalcCheckout() {
      const costoWrap = wrapCheck?.checked ? Number(wrapCheck.dataset.costo || 0) : 0;
      const esTienda = entregaSel?.value === 'recojo_tienda';
      const costoShip = esTienda ? 0 : Number(entregaSel?.dataset.envio || 0);

      if (dirGroup) dirGroup.style.display = esTienda ? 'none' : 'block';
      if (dirInput) dirInput.required = !esTienda;

      const total = Math.max(0, subtotalBase + costoWrap + costoShip - descuentoActual);
      if (sumEnvoltura) sumEnvoltura.textContent = `S/ ${costoWrap.toFixed(2)}`;
      if (sumEnvio) sumEnvio.textContent = `S/ ${costoShip.toFixed(2)}`;
      if (sumDescuento) sumDescuento.textContent = `-S/ ${descuentoActual.toFixed(2)}`;
      if (sumTotal) sumTotal.textContent = `S/ ${total.toFixed(2)}`;
    }

    wrapCheck?.addEventListener('change', recalcCheckout);
    entregaSel?.addEventListener('change', recalcCheckout);
    recalcCheckout();

    document.getElementById('applyCouponBtn')?.addEventListener('click', async () => {
      const code = document.getElementById('codigo_cupon')?.value || '';
      const fb = document.getElementById('couponFeedback');
      const res = await fetch(`${baseUrl}/api/cupon/validar?codigo=${encodeURIComponent(code)}&subtotal=${subtotalBase}`);
      const json = await res.json();
      if (json.data) {
        descuentoActual = json.data.valido ? Number(json.data.descuento || 0) : 0;
        if (fb) {
          fb.textContent = json.data.mensaje || '';
          fb.style.color = json.data.valido ? '#2E9E6B' : '#E84A8A';
        }
        recalcCheckout();
      }
    });
  }

  refreshCart();
})();