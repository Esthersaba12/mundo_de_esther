<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container auth-wrapper" style="max-width:680px;">
    <div class="auth-card">
      <div class="auth-card__head">
        <span class="pill-badge"><i class="fa-solid fa-circle-check"></i> Pedido Registrado</span>
        <h1>¡Gracias por tu compra en Mundo de Esther!</h1>
        <p>Código de pedido: <strong><?= $this->e($pedido['codigo_pedido']) ?></strong> • Estado: <b><?= $this->e($pedido['estado']) ?></b></p>
      </div>

      <ul class="detail-specs">
        <li><strong>Cliente:</strong> <?= $this->e($pedido['nombre_cliente']) ?> (<?= $this->e($pedido['telefono_cliente']) ?>)</li>
        <li><strong>Modalidad:</strong> <?= $pedido['tipo_entrega'] === 'recojo_tienda' ? 'Recojo en tienda' : 'Envío a domicilio (' . $this->e($pedido['direccion_entrega']) . ')' ?></li>
        <li><strong>Envoltura de regalo:</strong> <?= (int)$pedido['envoltura_regalo'] === 1 ? 'Sí (+S/ ' . number_format((float)$pedido['costo_envoltura'], 2) . ')' : 'No' ?></li>
        <?php if (!empty($pedido['dedicatoria_general'])): ?>
          <li><strong>Dedicatoria:</strong> "<?= $this->e($pedido['dedicatoria_general']) ?>"</li>
        <?php endif; ?>
      </ul>

      <div class="summary-items">
        <?php foreach ($pedido['items'] as $it): ?>
          <div class="summary-item-row">
            <span><?= (int)$it['cantidad'] ?>× <?= $this->e($it['nombre']) ?></span>
            <strong>S/ <?= number_format((float)$it['subtotal'], 2) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="summary-line summary-total" style="margin-top:1rem;">
        <span>Total del pedido</span>
        <span>S/ <?= number_format((float)$pedido['total'], 2) ?></span>
      </div>

      <div class="detail-actions" style="margin-top:1.5rem;">
        <a href="<?= BASE_URL ?>/catalogo" class="btn btn-primary">Seguir comprando</a>
        <span class="btn btn-outline" style="cursor:default;">Pagos Yape/Plin y WhatsApp en v5</span>
      </div>
    </div>
  </div>
</section>