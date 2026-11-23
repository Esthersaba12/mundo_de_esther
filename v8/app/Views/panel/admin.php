<?php
declare(strict_types=1);
$user = $_SESSION['user'] ?? [];
?>
<section class="section-block">
  <div class="container">
    <div class="role-banner" style="margin-bottom:1.5rem;">
      <div>
        <span class="pill-badge"><i class="fa-solid fa-crown"></i> Panel Administrativo Integral</span>
        <h1>Bienvenida, <?= $this->e($user['nombre'] ?? 'Admin') ?></h1>
        <p style="color:var(--text-muted);margin:0;">Resumen ejecutivo de ventas web, mostrador POS e inventario de Mundo de Esther.</p>
      </div>
      <div class="detail-actions">
        <a href="<?= BASE_URL ?>/inventario" class="btn btn-primary">
          <i class="fa-solid fa-boxes-stacked"></i> Inventario &amp; Kardex
        </a>
        <a href="<?= BASE_URL ?>/pos" class="btn btn-soft">
          <i class="fa-solid fa-cash-register"></i> Ir al POS
        </a>
        <a href="<?= BASE_URL ?>/gestion/pedidos" class="btn btn-outline">
          <i class="fa-solid fa-list-check"></i> Pedidos Web
        </a>
      </div>
    </div>

    <!-- Tarjetas KPI -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <span>Ingresos Totales</span>
        <strong>S/ <?= number_format((float)$kpis['ingresos_totales'], 2) ?></strong>
        <small>Web + Tienda física</small>
      </div>
      <div class="kpi-card">
        <span>Ventas Web</span>
        <strong>S/ <?= number_format((float)$kpis['ventas_web'], 2) ?></strong>
        <small><?= (int)$kpis['pedidos_pendientes'] ?> pedidos pendientes</small>
      </div>
      <div class="kpi-card">
        <span>Ventas POS Tienda</span>
        <strong>S/ <?= number_format((float)$kpis['ventas_pos'], 2) ?></strong>
        <small>Mostrador presencial</small>
      </div>
      <div class="kpi-card">
        <span>Stock Total</span>
        <strong><?= (int)$kpis['unidades_stock'] ?> und.</strong>
        <small style="color:<?= (int)$kpis['alertas_bajo'] > 0 ? '#D65A9A' : 'inherit' ?>;">
          <?= (int)$kpis['alertas_bajo'] ?> alertas de stock bajo
        </small>
      </div>
    </div>

    <!-- Alertas de Reposición -->
    <?php if (!empty($stockCritico)): ?>
      <div style="margin-top:2rem;">
        <h2><i class="fa-solid fa-triangle-exclamation" style="color:var(--gold-accent);"></i> Peluches por Reponer</h2>
        <div class="table-responsive-wrap" style="margin-top:0.75rem;">
          <table class="data-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Peluche</th>
                <th>Stock Actual</th>
                <th>Mínimo</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($stockCritico as $sc): ?>
                <tr>
                  <td><strong><?= $this->e($sc['codigo_sku']) ?></strong></td>
                  <td><?= $this->e($sc['nombre']) ?></td>
                  <td><span class="stock-badge is-low"><?= (int)$sc['stock_actual'] ?> und.</span></td>
                  <td><?= (int)$sc['stock_minimo'] ?> und.</td>
                  <td><a href="<?= BASE_URL ?>/inventario?bajo=1" class="btn btn-soft btn-sm">Reponer stock</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>