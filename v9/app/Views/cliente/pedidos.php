<?php
declare(strict_types=1);
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-box-open"></i> Seguimiento de Compras</span>
      <h1>Mis Pedidos</h1>
      <p>Consulta el estado de tus pedidos en Mundo de Esther y completa o revisa tu pago por Yape.</p>
    </div>

    <?php if (empty($pedidos)): ?>
      <div class="auth-card" style="text-align:center;">
        <p style="color:var(--text-muted);">Aún no tienes pedidos registrados con tu cuenta.</p>
        <a href="<?= BASE_URL ?>/catalogo" class="btn btn-primary" style="margin-top:0.75rem;">
          <i class="fa-solid fa-paw"></i> Explorar Catálogo
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Fecha</th>
              <th>Entrega</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Pago</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pedidos as $ped): ?>
              <tr>
                <td><strong><?= $this->e((string)$ped['codigo_pedido']) ?></strong></td>
                <td><small><?= $this->e((string)$ped['created_at']) ?></small></td>
                <td><?= ($ped['tipo_entrega'] ?? '') === 'recojo_tienda' ? 'Tienda' : 'Delivery' ?></td>
                <td><strong>S/ <?= number_format((float)$ped['total'], 2) ?></strong></td>
                <td><span class="stock-badge"><?= $this->e((string)$ped['estado']) ?></span></td>
                <td>
                  <a href="<?= BASE_URL ?>/pedido/pago?codigo=<?= urlencode((string)$ped['codigo_pedido']) ?>" class="btn btn-soft btn-sm">
                    <i class="fa-solid fa-qrcode"></i> Yape / Detalle
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>