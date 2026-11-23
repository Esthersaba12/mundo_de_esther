<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container">
    <div class="role-banner role-banner--admin">
      <div>
        <span class="pill-badge">Rol: Administrador</span>
        <h1>Panel de Control General</h1>
        <p>Acceso total habilitado para <strong><?= $this->e($_SESSION['user']['nombre'] ?? '') ?></strong></p>
      </div>
    </div>

    <div class="categories-grid" style="margin-top:1.5rem;">
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-boxes-stacked"></i></div>
        <h3>Inventario Transaccional</h3>
        <p>Control ACID de entradas, salidas y alertas de stock bajo (v8).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-chart-line"></i></div>
        <h3>Dashboard Financiero</h3>
        <p>Métricas de ventas, ganancias, ticket promedio y gráficos Chart.js (v9).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-users-gear"></i></div>
        <h3>Gestión de Roles</h3>
        <p>Middleware activo protegiendo rutas de Cliente, Staff y Administrador.</p>
      </div>
    </div>
  </div>
</section>