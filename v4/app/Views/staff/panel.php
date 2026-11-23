<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container">
    <div class="role-banner role-banner--staff">
      <div>
        <span class="pill-badge">Rol: Staff / Vendedor</span>
        <h1>Centro Operativo de Tienda</h1>
        <p>Operador activo: <strong><?= $this->e($_SESSION['user']['nombre'] ?? '') ?></strong></p>
      </div>
    </div>

    <div class="categories-grid" style="margin-top:1.5rem;">
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-cash-register"></i></div>
        <h3>Punto de Venta (POS)</h3>
        <p>Venta rápida en tienda física y descuento inmediato de stock (se activa en v6).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-truck-fast"></i></div>
        <h3>Central de Despacho</h3>
        <p>Monitoreo en tiempo real de pedidos pendientes y listos para entrega (se activa en v7).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-receipt"></i></div>
        <h3>Tickets 80mm</h3>
        <p>Impresión térmica optimizada para comprobantes de tienda (se activa en v7).</p>
      </div>
    </div>
  </div>
</section>