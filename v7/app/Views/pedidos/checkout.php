<?php
declare(strict_types=1);
$totalEstimado = $subtotal + $costoEnvio;
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-gift"></i> Checkout con Detalles de Regalo</span>
      <h1>Finalizar tu Pedido</h1>
      <p>Personaliza tu envoltura, tarjeta dedicatoria y modalidad de entrega.</p>
    </div>

    <div class="checkout-grid">
      <form action="<?= BASE_URL ?>/checkout/procesar" method="POST" id="checkoutForm" class="auth-card">
        <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">

        <h3>1. Datos de Contacto</h3>
        <div class="form-group">
          <label for="nombre_cliente">Nombre completo *</label>
          <input type="text" id="nombre_cliente" name="nombre_cliente" required minlength="3"
                 value="<?= $this->e($usuarioActual['nombre'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="telefono_cliente">Teléfono / Celular *</label>
            <input type="tel" id="telefono_cliente" name="telefono_cliente" required minlength="6"
                   placeholder="951000000" class="form-control">
          </div>
          <div class="form-group">
            <label for="email_cliente">Correo (opcional)</label>
            <input type="email" id="email_cliente" name="email_cliente"
                   value="<?= $this->e($usuarioActual['email'] ?? '') ?>" class="form-control">
          </div>
        </div>

        <h3>2. Entrega o Recojo en Tienda</h3>
        <div class="form-group">
          <label for="tipo_entrega">Modalidad de entrega</label>
          <select id="tipo_entrega" name="tipo_entrega" class="form-control" data-envio="<?= number_format($costoEnvio, 2, '.', '') ?>">
            <option value="delivery">Envío a domicilio (+S/ <?= number_format($costoEnvio, 2) ?>)</option>
            <option value="recojo_tienda">Recojo en tienda (Gratis - S/ 0.00)</option>
          </select>
        </div>

        <div class="form-group" id="direccionGroup">
          <label for="direccion_entrega">Dirección de entrega *</label>
          <input type="text" id="direccion_entrega" name="direccion_entrega"
                 placeholder="Jr. / Av., referencia y distrito" class="form-control">
        </div>

        <h3>3. Envoltura de Regalo y Dedicatoria</h3>
        <label class="checkbox-card" style="margin-bottom:0.9rem;">
          <input type="checkbox" id="envoltura_regalo" name="envoltura_regalo" value="1"
                 data-costo="<?= number_format($costoEnvoltura, 2, '.', '') ?>">
          <span>
            <strong>Envoltura especial de regalo (+S/ <?= number_format($costoEnvoltura, 2) ?>)</strong>
            <small style="display:block;color:var(--text-muted);">Papel pastel, cinta y presentación lista para regalar.</small>
          </span>
        </label>

        <div class="form-group">
          <label for="dedicatoria_general">Tarjeta de dedicatoria personalizada</label>
          <textarea id="dedicatoria_general" name="dedicatoria_general" rows="3" maxlength="250"
                    placeholder="Escribe aquí tu mensaje especial para esa persona..." class="form-control"></textarea>
        </div>

        <h3>4. Cupón de Descuento</h3>
        <div class="coupon-row">
          <input type="text" id="codigo_cupon" name="codigo_cupon" placeholder="Ej. ESTHER10 o DULCE15" class="form-control">
          <button type="button" id="applyCouponBtn" class="btn btn-soft btn-sm">Aplicar</button>
        </div>
        <small id="couponFeedback" class="coupon-feedback"></small>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.3rem;">
          <i class="fa-solid fa-check-circle"></i> Confirmar Pedido y Reservar Stock
        </button>
      </form>

      <!-- Resumen lateral -->
      <aside class="auth-card checkout-summary">
        <h3>Resumen de Compra</h3>
        <div class="summary-items">
          <?php foreach ($items as $it): ?>
            <div class="summary-item-row">
              <div>
                <strong><?= $this->e($it['nombre']) ?></strong>
                <small style="display:block;color:var(--text-muted);">
                  <?= (int)$it['cantidad'] ?> × S/ <?= number_format((float)$it['precio_unitario'], 2) ?>
                </small>
                <?php if (!empty($it['dedicatoria'])): ?>
                  <small style="display:block;color:#D65A9A;">Nota: "<?= $this->e($it['dedicatoria']) ?>"</small>
                <?php endif; ?>
              </div>
              <span>S/ <?= number_format((float)$it['subtotal_item'], 2) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <hr style="border:0;border-top:1px solid var(--border-soft);margin:1rem 0;">

        <div class="summary-line">
          <span>Subtotal productos</span>
          <strong id="sumSubtotal" data-val="<?= number_format((float)$subtotal, 2, '.', '') ?>">S/ <?= number_format((float)$subtotal, 2) ?></strong>
        </div>
        <div class="summary-line">
          <span>Envoltura de regalo</span>
          <strong id="sumEnvoltura">S/ 0.00</strong>
        </div>
        <div class="summary-line">
          <span>Envío</span>
          <strong id="sumEnvio">S/ <?= number_format((float)$costoEnvio, 2) ?></strong>
        </div>
        <div class="summary-line">
          <span>Descuento cupón</span>
          <strong id="sumDescuento" style="color:#2E9E6B;">-S/ 0.00</strong>
        </div>
        <div class="summary-line summary-total">
          <span>Total a pagar</span>
          <strong id="sumTotal">S/ <?= number_format((float)$totalEstimado, 2) ?></strong>
        </div>
      </aside>
    </div>
  </div>
</section>