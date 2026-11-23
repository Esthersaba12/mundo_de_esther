<?php
declare(strict_types=1);
?>
  </main>

  <footer class="site-footer">
    <div class="container footer__inner">
      <div>
        <div class="brand-logo brand-logo--footer">
          <span class="brand-logo__text">Mundo de Esther</span>
          <i class="fa-solid fa-heart brand-logo__icon"></i>
        </div>
        <p>Peluches tiernos con diseño cálido, compra segura y atención cercana.</p>
      </div>
      <div class="footer__meta">
        <span>© <?= date('Y') ?> Mundo de Esther</span>
        <span>•</span>
        <span>PHP 8 + PDO + MySQL</span>
      </div>
    </div>
  </footer>

  <!-- Barra de navegación inferior fija en móvil -->
  <nav class="mobile-bottom-nav" aria-label="Navegación móvil">
    <a href="<?= BASE_URL ?>/" class="mobile-nav-item is-active">
      <i class="fa-solid fa-house"></i>
      <span>Inicio</span>
    </a>
    <a href="#categorias" class="mobile-nav-item">
      <i class="fa-solid fa-layer-group"></i>
      <span>Categorías</span>
    </a>
    <a href="#catalogo" class="mobile-nav-item">
      <i class="fa-solid fa-heart"></i>
      <span>Peluches</span>
    </a>
    <a href="#versiones" class="mobile-nav-item">
      <i class="fa-solid fa-bag-shopping"></i>
      <span>Carrito</span>
    </a>
  </nav>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>