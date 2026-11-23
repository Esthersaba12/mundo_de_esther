(() => {
  'use strict';

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

  function buildPlushSvg(nombre, color) {
    const n = String(nombre || '').toLowerCase();
    const c = String(color || '').toLowerCase();
    let body = '#F8BBD0', stroke = '#EC407A', accent = '#FFF8E1', bg1 = '#FFF0F6', bg2 = '#E3F2FD';
    let ears = `<circle cx="112" cy="84" r="19" fill="${body}" stroke="${stroke}" stroke-width="3"/><circle cx="188" cy="84" r="19" fill="${body}" stroke="${stroke}" stroke-width="3"/>`;
    let extra = '';

    if (n.includes('conejo') || n.includes('conejit')) {
      body = '#E1BEE7'; stroke = '#AB47BC'; bg1 = '#F3E5F5'; bg2 = '#FCE4EC';
      ears = `<ellipse cx="128" cy="68" rx="15" ry="42" fill="${body}" stroke="${stroke}" stroke-width="3"/><ellipse cx="172" cy="68" rx="15" ry="42" fill="${body}" stroke="${stroke}" stroke-width="3"/><ellipse cx="128" cy="70" rx="7" ry="28" fill="#F48FB1"/><ellipse cx="172" cy="70" rx="7" ry="28" fill="#F48FB1"/>`;
    } else if (n.includes('unicorn')) {
      body = '#F3E5F5'; stroke = '#CE93D8'; accent = '#FFD54F'; bg1 = '#E8EAF6'; bg2 = '#FCE4EC';
      ears = `<polygon points="150,32 138,76 162,76" fill="#FFD54F" stroke="#F57F17" stroke-width="2.5"/><circle cx="114" cy="86" r="18" fill="${body}" stroke="${stroke}" stroke-width="3"/><circle cx="186" cy="86" r="18" fill="${body}" stroke="${stroke}" stroke-width="3"/>`;
    } else if (n.includes('capibar')) {
      body = '#D7CCC8'; stroke = '#8D6E63'; bg1 = '#EFEBE9'; bg2 = '#FFF3E0';
    } else if (n.includes('panda')) {
      body = '#FFFFFF'; stroke = '#37474F'; bg1 = '#ECEFF1'; bg2 = '#F3E5F5';
      ears = `<circle cx="112" cy="82" r="18" fill="#37474F"/><circle cx="188" cy="82" r="18" fill="#37474F"/>`;
      extra = `<ellipse cx="133" cy="108" rx="10" ry="13" transform="rotate(-15 133 108)" fill="#37474F"/><ellipse cx="167" cy="108" rx="10" ry="13" transform="rotate(15 167 108)" fill="#37474F"/>`;
    } else if (n.includes('gat') || n.includes('michi')) {
      body = '#FFE0B2'; stroke = '#FB8C00'; bg1 = '#FFF3E0'; bg2 = '#E1F5FE';
      ears = `<polygon points="112,86 122,48 140,76" fill="${body}" stroke="${stroke}" stroke-width="3"/><polygon points="188,86 178,48 160,76" fill="${body}" stroke="${stroke}" stroke-width="3"/>`;
    } else if (n.includes('perr') || n.includes('cachorr')) {
      body = '#FFCCBC'; stroke = '#E64A19'; bg1 = '#FBE9E7'; bg2 = '#EDE7F6';
    }

    if (c.includes('celeste') || c.includes('azul')) {
      body = '#B3E5FC'; stroke = '#0288D1';
    } else if (c.includes('lila') || c.includes('morad')) {
      body = '#E1BEE7'; stroke = '#8E24AA';
    } else if (c.includes('rosa') || c.includes('rosad')) {
      body = '#F8BBD0'; stroke = '#D81B60';
    } else if (c.includes('dorad') || c.includes('amarill')) {
      body = '#FFF59D'; stroke = '#FBC02D';
    }

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 220" width="100%" height="100%">`
      + `<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="${bg1}"/><stop offset="100%" stop-color="${bg2}"/></linearGradient></defs>`
      + `<rect width="300" height="220" fill="url(#bg)"/>`
      + `<circle cx="52" cy="46" r="18" fill="#FFF" opacity="0.65"/><circle cx="252" cy="52" r="24" fill="#FFF" opacity="0.55"/>`
      + ears
      + `<ellipse cx="150" cy="166" rx="44" ry="38" fill="${body}" stroke="${stroke}" stroke-width="3"/>`
      + `<ellipse cx="150" cy="168" rx="26" ry="23" fill="${accent}" opacity="0.85"/>`
      + `<ellipse cx="106" cy="154" rx="12" ry="20" transform="rotate(24 106 154)" fill="${body}" stroke="${stroke}" stroke-width="3"/>`
      + `<ellipse cx="194" cy="154" rx="12" ry="20" transform="rotate(-24 194 154)" fill="${body}" stroke="${stroke}" stroke-width="3"/>`
      + `<ellipse cx="150" cy="110" rx="46" ry="40" fill="${body}" stroke="${stroke}" stroke-width="3"/>`
      + extra
      + `<circle cx="133" cy="108" r="5.5" fill="#2B2438"/><circle cx="167" cy="108" r="5.5" fill="#2B2438"/>`
      + `<circle cx="131" cy="106" r="2" fill="#FFF"/><circle cx="165" cy="106" r="2" fill="#FFF"/>`
      + `<ellipse cx="121" cy="118" rx="7" ry="4.5" fill="#FF80AB" opacity="0.65"/><ellipse cx="179" cy="118" rx="7" ry="4.5" fill="#FF80AB" opacity="0.65"/>`
      + `<ellipse cx="150" cy="116" rx="6" ry="4.5" fill="#4E342E"/>`
      + `<path d="M142 123 Q150 130 158 123" fill="none" stroke="#4E342E" stroke-width="2.5" stroke-linecap="round"/>`
      + `</svg>`;

    return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
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

  // Filtros AJAX en vivo (v3 + v8 con ilustraciones por nombre)
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
        const imgRel = String(p.imagen_url || p.imagen || p.foto || '').trim();
        const imgFinal = imgRel
          ? `${baseUrl}/${escapeHtml(imgRel.replace(/^\/+/, ''))}`
          : buildPlushSvg(p.nombre, p.color);
        const mediaInner = `<img src="${imgFinal}" alt="${escapeHtml(p.nombre)}" style="width:100%;height:100%;object-fit:cover;">`;

        return `
          <article class="product-card">
            <div class="product-card__media">
              ${tagHtml}
              <button type="button" class="fav-btn ${esFav ? 'is-fav' : ''}" data-product-id="${p.id}" aria-label="Favorito">
                <i class="fa-${esFav ? 'solid' : 'regular'} fa-heart"></i>
              </button>
              <a href="${baseUrl}/producto?slug=${encodeURIComponent(p.slug)}" class="plush-placeholder" style="width:100%;height:100%;">
                ${mediaInner}
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

  // Punto de Venta (POS) interactivo
  const posGrid = document.getElementById('posProductsGrid');
  if (posGrid) {
    const posSearch = document.getElementById('posSearchInput');
    const posTicketItems = document.getElementById('posTicketItems');
    const posSubtotalEl = document.getElementById('posSubtotalDisplay');
    const posTotalEl = document.getElementById('posTotalDisplay');
    const posDescEl = document.getElementById('posDescuento');
    const posMetodoEl = document.getElementById('posMetodo');
    const posCashRow = document.getElementById('posCashRow');
    const posRecibidoEl = document.getElementById('posRecibido');
    const posVueltoEl = document.getElementById('posVueltoDisplay');
    const ticketMap = new Map();
    let posTimer = null;

    function renderPosTicket() {
      let subtotal = 0;
      if (!ticketMap.size) {
        posTicketItems.innerHTML = `<p style="color:var(--text-muted);text-align:center;margin:1.5rem 0;">
          Selecciona peluches de la izquierda para iniciar la venta.
        </p>`;
      } else {
        posTicketItems.innerHTML = Array.from(ticketMap.values()).map((it) => {
          const sub = it.precio * it.cantidad;
          subtotal += sub;
          return `
            <div class="cart-item-card">
              <div class="cart-item-card__top">
                <div>
                  <strong>${escapeHtml(it.nombre)}</strong>
                  <small style="display:block;color:var(--text-muted);">${escapeHtml(it.sku)} • S/ ${it.precio.toFixed(2)}</small>
                </div>
                <button type="button" class="icon-btn icon-btn--sm pos-del-item" data-id="${it.id}">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
              <div class="cart-item-card__bottom">
                <div class="qty-stepper">
                  <button type="button" class="qty-btn pos-qty-btn" data-id="${it.id}" data-delta="-1">-</button>
                  <span>${it.cantidad}</span>
                  <button type="button" class="qty-btn pos-qty-btn" data-id="${it.id}" data-delta="1">+</button>
                </div>
                <strong>S/ ${sub.toFixed(2)}</strong>
              </div>
            </div>`;
        }).join('');
      }

      const desc = Math.min(subtotal, Math.max(0, Number(posDescEl?.value || 0)));
      const total = Math.max(0, subtotal - desc);

      if (posSubtotalEl) posSubtotalEl.textContent = `S/ ${subtotal.toFixed(2)}`;
      if (posTotalEl) posTotalEl.textContent = `S/ ${total.toFixed(2)}`;

      const esEfectivo = posMetodoEl?.value === 'efectivo';
      if (posCashRow) posCashRow.style.display = esEfectivo ? 'grid' : 'none';

      const recibido = Number(posRecibidoEl?.value || 0);
      const vuelto = esEfectivo ? Math.max(0, recibido - total) : 0;
      if (posVueltoEl) posVueltoEl.textContent = `S/ ${vuelto.toFixed(2)}`;
    }

    posGrid.addEventListener('click', (e) => {
      const tile = e.target.closest('.pos-product-tile');
      if (!tile) return;
      const id = Number(tile.dataset.id);
      const stock = Number(tile.dataset.stock);
      const actual = ticketMap.get(id);

      if (actual) {
        if (actual.cantidad < stock) actual.cantidad += 1;
      } else if (stock > 0) {
        ticketMap.set(id, {
          id,
          sku: tile.dataset.sku,
          nombre: tile.dataset.nombre,
          precio: Number(tile.dataset.precio),
          stock,
          cantidad: 1
        });
      }
      renderPosTicket();
    });

    posTicketItems.addEventListener('click', (e) => {
      const qBtn = e.target.closest('.pos-qty-btn');
      if (qBtn) {
        const id = Number(qBtn.dataset.id);
        const delta = Number(qBtn.dataset.delta);
        const item = ticketMap.get(id);
        if (item) {
          const next = item.cantidad + delta;
          if (next <= 0) ticketMap.delete(id);
          else if (next <= item.stock) item.cantidad = next;
          renderPosTicket();
        }
        return;
      }
      const dBtn = e.target.closest('.pos-del-item');
      if (dBtn) {
        ticketMap.delete(Number(dBtn.dataset.id));
        renderPosTicket();
      }
    });

    document.getElementById('posClearTicketBtn')?.addEventListener('click', () => {
      ticketMap.clear();
      renderPosTicket();
    });

    posDescEl?.addEventListener('input', renderPosTicket);
    posMetodoEl?.addEventListener('change', renderPosTicket);
    posRecibidoEl?.addEventListener('input', renderPosTicket);

    posSearch?.addEventListener('input', () => {
      clearTimeout(posTimer);
      posTimer = setTimeout(async () => {
        const res = await fetch(`${baseUrl}/api/pos/buscar?q=${encodeURIComponent(posSearch.value)}`);
        const data = await res.json();
        if (!data.ok) return;
        posGrid.innerHTML = data.productos.map((p) => `
          <button type="button" class="pos-product-tile"
                  data-id="${p.id}" data-sku="${escapeHtml(p.codigo_sku)}"
                  data-nombre="${escapeHtml(p.nombre)}" data-precio="${Number(p.precio_venta).toFixed(2)}"
                  data-stock="${p.stock_actual}">
            <span class="pos-sku">${escapeHtml(p.codigo_sku)}</span>
            <strong>${escapeHtml(p.nombre)}</strong>
            <small>${escapeHtml(p.tamano)} • Stock: ${p.stock_actual}</small>
            <span class="pos-price">S/ ${Number(p.precio_venta).toFixed(2)}</span>
          </button>
        `).join('');
      }, 220);
    });

    document.getElementById('posCheckoutBtn')?.addEventListener('click', async () => {
      if (!ticketMap.size) {
        alert('Agrega al menos un peluche al ticket.');
        return;
      }
      const items = Array.from(ticketMap.values()).map((it) => ({
        producto_id: it.id,
        cantidad: it.cantidad
      }));

      const res = await fetch(`${baseUrl}/api/pos/vender`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          csrf_token: csrfToken,
          cliente_nombre: document.getElementById('posCliente')?.value || 'Cliente Mostrador',
          cliente_documento: document.getElementById('posDoc')?.value || '',
          metodo_pago: posMetodoEl?.value || 'efectivo',
          descuento: posDescEl?.value || 0,
          monto_recibido: posRecibidoEl?.value || 0,
          items
        })
      });

      const data = await res.json();
      if (!res.ok || !data.ok) {
        alert(data.message || 'No se pudo registrar la venta.');
        return;
      }

      window.location.href = `${baseUrl}/pos/ticket?codigo=${encodeURIComponent(data.codigo_venta)}`;
    });

    renderPosTicket();
  }

  refreshCart();
})();