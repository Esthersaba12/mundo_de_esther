<?php
declare(strict_types=1);
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-cash-register"></i> Venta Presencial en Tienda</span>
      <h1>Punto de Venta (POS)</h1>
      <p>Busca peluches por nombre o código SKU, arma el ticket y descuenta el inventario automáticamente.</p>
    </div>

    <div class="pos-layout">
      <!-- Buscador y parrilla rápida de peluches -->
      <div class="auth-card">
        <div class="filter-search">
          <i class="fa-solid fa-barcode"></i>
          <input type="search" id="posSearchInput" class="form-control"
                 placeholder="Escribe nombre del peluche o código SKU (ej. MDE-OSO-001)..." autofocus>
        </div>

        <div class="pos-products-grid" id="posProductsGrid">
          <?php foreach ($productos as $p): ?>
            <button type="button"
                    class="pos-product-tile"
                    data-id="<?= (int)$p['id'] ?>"
                    data-sku="<?= $this->e($p['codigo_sku']) ?>"
                    data-nombre="<?= $this->e($p['nombre']) ?>"
                    data-precio="<?= number_format((float)$p['precio_venta'], 2, '.', '') ?>"
                    data-stock="<?= (int)$p['stock_actual'] ?>">
              <span class="pos-sku"><?= $this->e($p['codigo_sku']) ?></span>
              <strong><?= $this->e($p['nombre']) ?></strong>
              <small><?= $this->e($p['tamano']) ?> • Stock: <?= (int)$p['stock_actual'] ?></small>
              <span class="pos-price">S/ <?= number_format((float)$p['precio_venta'], 2) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Ticket actual y cobro -->
      <aside class="auth-card pos-ticket-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.85rem;">
          <h3 style="margin:0;"><i class="fa-solid fa-receipt"></i> Ticket Actual</h3>
          <button type="button" id="posClearTicketBtn" class="btn btn-outline btn-sm">Vaciar</button>
        </div>

        <div id="posTicketItems" class="pos-ticket-items">
          <p style="color:var(--text-muted);text-align:center;margin:1.5rem 0;">Selecciona peluches de la izquierda para iniciar la venta.</p>
        </div>

        <hr style="border:0;border-top:1px solid var(--border-soft);margin:1rem 0;">

        <div class="form-row-2">
          <div class="form-group">
            <label for="posCliente">Cliente</label>
            <input type="text" id="posCliente" value="Cliente Mostrador" class="form-control">
          </div>
          <div class="form-group">
            <label for="posDoc">DNI / RUC (opcional)</label>
            <input type="text" id="posDoc" placeholder="Opcional" class="form-control">
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="posMetodo">Método de pago</label>
            <select id="posMetodo" class="form-control">
              <option value="efectivo">Efectivo</option>
              <option value="yape">Yape (999199969)</option>
              <option value="tarjeta">Tarjeta POS</option>
            </select>
          </div>
          <div class="form-group">
            <label for="posDescuento">Descuento S/</label>
            <input type="number" id="posDescuento" min="0" step="0.50" value="0.00" class="form-control">
          </div>
        </div>

        <div class="form-row-2" id="posCashRow">
          <div class="form-group">
            <label for="posRecibido">Efectivo recibido S/</label>
            <input type="number" id="posRecibido" min="0" step="0.50" value="0.00" class="form-control">
          </div>
          <div class="form-group">
            <label>Vuelto</label>
            <div class="form-control" style="font-weight:700;color:#2E9E6B;" id="posVueltoDisplay">S/ 0.00</div>
          </div>
        </div>

        <div class="summary-line">
          <span>Subtotal</span>
          <strong id="posSubtotalDisplay">S/ 0.00</strong>
        </div>
        <div class="summary-line summary-total">
          <span>Total a cobrar</span>
          <strong id="posTotalDisplay">S/ 0.00</strong>
        </div>

        <button type="button" id="posCheckoutBtn" class="btn btn-primary btn-block" style="margin-top:0.85rem;">
          <i class="fa-solid fa-check-double"></i> Registrar Venta y Emitir Ticket
        </button>
      </aside>
    </div>

    <!-- Últimas ventas POS -->
    <div style="margin-top:2.2rem;">
      <h2>Últimas Ventas en Mostrador</h2>
      <div class="table-responsive-wrap" style="margin-top:0.9rem;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Ticket</th>
              <th>Cliente</th>
              <th>Método</th>
              <th>Total</th>
              <th>Vendedor</th>
              <th>Fecha</th>
              <th>Comprobante</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recientes as $v): ?>
              <tr>
                <td><strong><?= $this->e($v['codigo_venta']) ?></strong></td>
                <td><?= $this->e($v['cliente_nombre']) ?></td>
                <td><span class="stock-badge"><?= strtoupper($this->e($v['metodo_pago'])) ?></span></td>
                <td><strong>S/ <?= number_format((float)$v['total'], 2) ?></strong></td>
                <td><?= $this->e($v['vendedor']) ?></td>
                <td><?= $this->e($v['created_at']) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/pos/ticket?codigo=<?= urlencode($v['codigo_venta']) ?>" class="btn btn-soft btn-sm">
                    <i class="fa-solid fa-print"></i> Ver Ticket
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>