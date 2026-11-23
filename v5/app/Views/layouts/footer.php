<?php
declare(strict_types=1);
?>
  </main>

  <!-- Panel Lateral Deslizable del Carrito (v4) -->
  <div class="cart-backdrop" id="cartBackdrop"></div>
  <aside class="cart-drawer" id="cartDrawer" aria-label="Carrito de compras">
    <div class="cart-drawer__head">
      <h3><i class="fa-solid fa-bag-shopping"></i> Tu Carrito</h3>
      <button type="button" class="icon-btn icon-btn--sm" id="closeCartBtn" aria-label="Cerrar carrito">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="cart-drawer__body" id="cartItemsContainer">
      <p style="color:var(--text-muted);text-align:center;">Cargando carrito...</p>
    </div>

    <div class="cart-drawer__foot">
      <div class="summary-line">
        <span>Subtotal</span>
        <strong id="drawerSubtotal">S/ 0.00</strong>
      </div>
      <a href="<?= BASE_URL ?>/checkout" class="btn btn-primary btn-block" id="goCheckoutBtn">
        <i class="fa-solid fa-arrow-right"></i> Ir a Checkout
      </a>
    </div>
  </aside>

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
        <span>PHP 8 + PDO ACID + MySQL</span>
      </div>
    </div>
  </footer>

  <!-- Barra de navegación inferior fija en móvil -->
  <nav class="mobile-bottom-nav" aria-label="Navegación móvil">
    <a href="<?= BASE_URL ?>/" class="mobile-nav-item">
      <i class="fa-solid fa-house"></i>
      <span>Inicio</span>
    </a>
    <a href="<?= BASE_URL ?>/catalogo" class="mobile-nav-item">
      <i class="fa-solid fa-heart"></i>
      <span>Peluches</span>
    </a>
    <button type="button" class="mobile-nav-item open-cart-trigger" style="background:none;border:none;">
      <i class="fa-solid fa-bag-shopping"></i>
      <span>Carrito (<span class="cart-count-badge">0</span>)</span>
    </button>
    <a href="<?= BASE_URL ?>/login" class="mobile-nav-item">
      <i class="fa-solid fa-user"></i>
      <span>Cuenta</span>
    </a>
  </nav>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>