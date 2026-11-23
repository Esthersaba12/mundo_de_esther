(() => {
  'use strict';

  // Modo oscuro / claro con persistencia en localStorage
  const htmlEl = document.documentElement;
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  const savedTheme = localStorage.getItem('mde_theme') || 'light';
  applyTheme(savedTheme);

  function applyTheme(theme) {
    htmlEl.setAttribute('data-theme', theme);
    localStorage.setItem('mde_theme', theme);
    if (themeIcon) {
      themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
  }

  themeToggle?.addEventListener('click', () => {
    const current = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(current);
  });

  // Carrusel de destacados
  const slides = Array.from(document.querySelectorAll('.carousel-slide'));
  const prevBtn = document.getElementById('prevSlide');
  const nextBtn = document.getElementById('nextSlide');
  let activeIndex = 0;

  function showSlide(idx) {
    if (!slides.length) return;
    activeIndex = (idx + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.classList.toggle('is-active', i === activeIndex);
    });
  }

  prevBtn?.addEventListener('click', () => showSlide(activeIndex - 1));
  nextBtn?.addEventListener('click', () => showSlide(activeIndex + 1));

  if (slides.length > 1) {
    setInterval(() => showSlide(activeIndex + 1), 5000);
  }

  // Menú móvil rápido
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

  // ========================================================================
  // v3: Filtros AJAX en vivo, Zoom de galería y Favoritos
  // ========================================================================
  const baseUrl = window.location.pathname.includes('/public')
    ? window.location.pathname.split('/public')[0] + '/public'
    : '';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const filterForm = document.getElementById('catalogFilterForm');
  const resultsGrid = document.getElementById('catalogResultsGrid');
  const countLabel = document.getElementById('catalogCount');
  let debounceTimer = null;

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

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
          <p>No encontramos peluches con esos filtros. Prueba limpiando algún criterio.</p>
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
              <a href="${baseUrl}/producto?slug=${encodeURIComponent(p.slug)}" class="btn btn-primary btn-sm btn-block" style="margin-top:0.85rem;">
                <i class="fa-solid fa-eye"></i> Ver Peluche
              </a>
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

  // Toggle de Favoritos (AJAX)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.fav-btn, .fav-btn-inline');
    if (!btn) return;
    const productId = btn.dataset.productId;
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
      btn.classList.toggle('is-fav', data.favorito);
      const icon = btn.querySelector('i');
      if (icon) icon.className = `fa-${data.favorito ? 'solid' : 'regular'} fa-heart`;
    }
  });

  // Zoom interactivo y cambio de miniaturas en detalle de producto
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
})();