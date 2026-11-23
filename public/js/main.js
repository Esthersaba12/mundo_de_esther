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
})();