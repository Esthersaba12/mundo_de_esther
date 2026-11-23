<?php
declare(strict_types=1);
$createdAtFmt = is_numeric($venta['created_at'] ?? null)
    ? date('Y-m-d H:i:s', (int)$venta['created_at'])
    : (string)($venta['created_at'] ?? '');
?>
<section class="section-block">
  <div class="container auth-wrapper" style="max-width:460px;">
    <div class="auth-card ticket-print-area" id="ticketPrintArea">
      <div class="auth-card__head" style="margin-bottom:1rem;">
        <span class="pill-badge"><i class="fa-solid fa-heart"></i> Comprobante de Venta POS</span>
        <h2><?= $this->e((string)APP_NAME) ?></h2>
        <p><?= $this->e((string)APP_SLOGAN) ?></p>
        <small>Ticket: <strong><?= $this->e((string)($venta['codigo_venta'] ?? '')) ?></strong> • <?= $this->e($createdAtFmt) ?></small>
      </div>

      <ul class="detail-specs" style="font-size:0.86rem;">
        <li><strong>Cliente:</strong> <?= $this->e((string)($venta['cliente_nombre'] ?? '')) ?><?= !empty($venta['cliente_documento']) ? ' (' . $this->e((string)$venta['cliente_documento']) . ')' : '' ?></li>
        <li><strong>Atendido por:</strong> <?= $this->e((string)($venta['vendedor_nombre'] ?? '')) ?></li>
        <li><strong>Método de pago:</strong> <?= strtoupper($this->e((string)($venta['metodo_pago'] ?? ''))) ?></li>
      </ul>

      <hr style="border:0;border-top:1px dashed var(--border-soft);margin:0.85rem 0;">

      <div class="summary-items">
        <?php foreach (($venta['items'] ?? []) as $it): ?>
          <div class="summary-item-row" style="font-size:0.88rem;">
            <span><?= (int)($it['cantidad'] ?? 1) ?>× <?= $this->e((string)($it['nombre'] ?? '')) ?></span>
            <strong>S/ <?= number_format((float)($it['subtotal'] ?? 0), 2) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>

      <hr style="border:0;border-top:1px dashed var(--border-soft);margin:0.85rem 0;">

      <div class="summary-line">
        <span>Subtotal</span>
        <span>S/ <?= number_format((float)($venta['subtotal'] ?? 0), 2) ?></span>
      </div>
      <?php if ((float)($venta['descuento'] ?? 0) > 0): ?>
        <div class="summary-line">
          <span>Descuento</span>
          <span>-S/ <?= number_format((float)($venta['descuento'] ?? 0), 2) ?></span>
        </div>
      <?php endif; ?>
      <div class="summary-line summary-total">
        <span>TOTAL PAGADO</span>
        <span>S/ <?= number_format((float)($venta['total'] ?? 0), 2) ?></span>
      </div>

      <?php if (($venta['metodo_pago'] ?? '') === 'efectivo'): ?>
        <div class="summary-line" style="font-size:0.85rem;">
          <span>Recibido / Vuelto</span>
          <span>S/ <?= number_format((float)($venta['monto_recibido'] ?? 0), 2) ?> / S/ <?= number_format((float)($venta['vuelto'] ?? 0), 2) ?></span>
        </div>
      <?php endif; ?>

      <div class="detail-actions no-print" style="margin-top:1.3rem;">
        <button type="button" onclick="window.print()" class="btn btn-primary" style="flex:1;">
          <i class="fa-solid fa-print"></i> Imprimir Ticket
        </button>
        <a href="<?= BASE_URL ?>/pos" class="btn btn-soft" style="flex:1;">
          Nueva Venta
        </a>
      </div>
    </div>
  </div>
</section>