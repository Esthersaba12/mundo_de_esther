<?php
declare(strict_types=1);
$qrPath = defined('YAPE_QR_IMAGE') ? YAPE_QR_IMAGE : 'assets/images/qr-yape.png';
$qrExists = file_exists(PUBLIC_PATH . '/' . ltrim($qrPath, '/'));
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-qrcode"></i> Pago con Yape y WhatsApp</span>
      <h1>Completa tu Pago — <?= $this->e($pedido['codigo_pedido']) ?></h1>
      <p>Estado actual del pedido: <strong><?= $this->e($pedido['estado']) ?></strong> • Total: <strong>S/ <?= number_format((float)$pedido['total'], 2) ?></strong></p>
    </div>

    <div class="payment-grid">
      <div class="auth-card">
        <h3>1. Escanea o Yapea</h3>
        <div class="qr-boxes">
          <div class="qr-card qr-card--yape">
            <span class="qr-badge">YAPE</span>
            <div class="qr-mock" style="width:180px;height:180px;padding:0.4rem;">
              <?php if ($qrExists): ?>
                <img src="<?= BASE_URL . '/' . ltrim($qrPath, '/') ?>"
                     alt="QR Yape Mundo de Esther"
                     style="width:100%;height:100%;object-fit:contain;border-radius:10px;">
              <?php else: ?>
                <i class="fa-solid fa-qrcode"></i>
                <strong>QR YAPE</strong>
                <small style="font-size:0.7rem;color:var(--text-muted);">Pon qr-yape.png en public/assets/images/</small>
              <?php endif; ?>
            </div>
            <p>Titular: <b><?= $this->e(YAPE_TITULAR) ?></b></p>
            <p>Celular: <b><?= $this->e(YAPE_NUMBER) ?></b></p>
          </div>
        </div>

        <hr style="border:0;border-top:1px solid var(--border-soft);margin:1.35rem 0;">

        <h3>2. ¿Prefieres coordinar directo por WhatsApp?</h3>
        <p style="font-size:0.88rem;color:var(--text-muted);">
          Genera automáticamente tu mensaje con el código del pedido, peluches, envoltura, dedicatoria y total.
        </p>
        <form action="<?= BASE_URL ?>/pedido/whatsapp" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="codigo_pedido" value="<?= $this->e($pedido['codigo_pedido']) ?>">
          <button type="submit" class="btn btn-whatsapp btn-block">
            <i class="fa-brands fa-whatsapp"></i> Pedir por WhatsApp
          </button>
        </form>
      </div>

      <div class="auth-card">
        <h3>3. Subir Comprobante de Yape</h3>
        <form action="<?= BASE_URL ?>/pedido/comprobante" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="codigo_pedido" value="<?= $this->e($pedido['codigo_pedido']) ?>">

          <div class="form-group">
            <label for="codigo_operacion">Código / Nro. de operación Yape</label>
            <input type="text" name="codigo_operacion" id="codigo_operacion"
                   placeholder="Ej. 0192834" class="form-control">
          </div>

          <div class="form-group">
            <label for="comprobante">Captura de pantalla (JPG, PNG, WEBP — máx. 4MB)</label>
            <input type="file" name="comprobante" id="comprobante" accept="image/jpeg,image/png,image/webp" class="form-control">
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-cloud-arrow-up"></i> Enviar Comprobante
          </button>
        </form>

        <?php if (!empty($pagos)): ?>
          <div style="margin-top:1.4rem;">
            <h4>Comprobantes / Registros del Pedido</h4>
            <?php foreach ($pagos as $pg): ?>
              <div class="payment-log-item">
                <div>
                  <strong><?= strtoupper($this->e($pg['metodo'])) ?></strong> • S/ <?= number_format((float)$pg['monto'], 2) ?>
                  <?php if (!empty($pg['codigo_operacion'])): ?>
                    <small style="display:block;color:var(--text-muted);">Op: <?= $this->e($pg['codigo_operacion']) ?></small>
                  <?php endif; ?>
                  <?php if (!empty($pg['comprobante_ruta'])): ?>
                    <a href="<?= BASE_URL . '/' . ltrim($pg['comprobante_ruta'], '/') ?>" target="_blank" style="font-size:0.82rem;color:#D65A9A;font-weight:600;">
                      Ver captura subida
                    </a>
                  <?php endif; ?>
                </div>
                <span class="stock-badge"><?= $this->e($pg['estado_pago']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>