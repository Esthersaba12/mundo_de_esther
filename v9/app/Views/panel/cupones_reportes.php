<?php
declare(strict_types=1);
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-ticket"></i> Promociones y Exportación</span>
      <h1>Cupones y Reportes CSV</h1>
      <p>Crea cupones para el checkout web y descarga reportes listos para Excel de ventas e inventario.</p>
    </div>

    <div class="dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-bottom:2rem;">
      <div class="auth-card" style="margin:0;">
        <h3><i class="fa-solid fa-globe"></i> Ventas Web</h3>
        <p style="color:var(--text-muted);"><?= (int)($webCount ?? 0) ?> pedidos registrados</p>
        <a href="<?= BASE_URL ?>/admin/reportes/csv?tipo=web" class="btn btn-primary btn-sm btn-block">
          <i class="fa-solid fa-file-csv"></i> Descargar CSV Web
        </a>
      </div>

      <div class="auth-card" style="margin:0;">
        <h3><i class="fa-solid fa-cash-register"></i> Ventas POS Tienda</h3>
        <p style="color:var(--text-muted);"><?= (int)($posCount ?? 0) ?> tickets emitidos</p>
        <a href="<?= BASE_URL ?>/admin/reportes/csv?tipo=pos" class="btn btn-soft btn-sm btn-block">
          <i class="fa-solid fa-file-csv"></i> Descargar CSV POS
        </a>
      </div>

      <div class="auth-card" style="margin:0;">
        <h3><i class="fa-solid fa-boxes-stacked"></i> Inventario Actual</h3>
        <p style="color:var(--text-muted);"><?= (int)($invCount ?? 0) ?> peluches en catálogo</p>
        <a href="<?= BASE_URL ?>/admin/reportes/csv?tipo=inventario" class="btn btn-primary btn-sm btn-block">
          <i class="fa-solid fa-file-csv"></i> Descargar CSV Stock
        </a>
      </div>
    </div>

    <div class="auth-card" style="max-width:740px;margin:0 auto 2rem;">
      <h3><i class="fa-solid fa-plus-circle"></i> Crear o Actualizar Cupón</h3>
      <form action="<?= BASE_URL ?>/admin/cupones/guardar" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->e((string)($_SESSION['csrf_token'] ?? '')) ?>">

        <div class="form-row-2">
          <div class="form-group">
            <label for="cp_codigo">Código del cupón *</label>
            <input type="text" name="codigo" id="cp_codigo" placeholder="Ej. REGALO15" required class="form-control">
          </div>
          <div class="form-group">
            <label for="cp_tipo">Tipo de descuento</label>
            <select name="tipo_descuento" id="cp_tipo" class="form-control">
              <option value="porcentaje">Porcentaje (%)</option>
              <option value="monto">Monto fijo (S/)</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="cp_valor">Valor del descuento *</label>
            <input type="number" name="valor" id="cp_valor" step="0.50" min="0.50" placeholder="15" required class="form-control">
          </div>
          <div class="form-group">
            <label for="cp_minimo">Compra mínima S/ (opcional)</label>
            <input type="number" name="monto_minimo" id="cp_minimo" step="1" min="0" value="0" class="form-control">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Cupón
        </button>
      </form>
    </div>

    <div class="table-responsive-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Mínimo S/</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cupones as $c): ?>
            <tr>
              <td><strong><?= $this->e((string)$c['codigo']) ?></strong></td>
              <td><?= $this->e((string)$c['tipo_descuento']) ?></td>
              <td>
                <?php if (($c['tipo_descuento'] ?? '') === 'porcentaje'): ?>
                  <?= number_format((float)$c['valor'], 0) ?>%
                <?php else: ?>
                  S/ <?= number_format((float)$c['valor'], 2) ?>
                <?php endif; ?>
              </td>
              <td>S/ <?= number_format((float)($c['monto_minimo'] ?? 0), 2) ?></td>
              <td><span class="stock-badge"><?= !empty($c['activo']) ? 'Activo' : 'Inactivo' ?></span></td>
              <td>
                <form action="<?= BASE_URL ?>/admin/cupones/toggle" method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= $this->e((string)($_SESSION['csrf_token'] ?? '')) ?>">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button type="submit" class="btn btn-soft btn-sm">
                    <?= !empty($c['activo']) ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>