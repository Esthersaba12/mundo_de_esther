<?php
declare(strict_types=1);
$isAdmin = ($_SESSION['user']['rol'] ?? '') === 'admin';
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-boxes-stacked"></i> Control de Stock y Kardex</span>
      <h1>Inventario de Peluches</h1>
      <p>Registra ingresos de mercadería, ajustes físicos, revisa alertas de reposición y consulta el historial de movimientos.</p>
    </div>

    <div class="roadmap-chips" style="margin-bottom:1.5rem;">
      <a href="<?= BASE_URL ?>/inventario" class="chip <?= !$soloBajo ? 'is-active' : '' ?>">Todo el inventario</a>
      <a href="<?= BASE_URL ?>/inventario?bajo=1" class="chip <?= $soloBajo ? 'is-active' : '' ?>">
        <i class="fa-solid fa-triangle-exclamation"></i> Alertas de stock bajo
      </a>
    </div>

    <div class="inventory-top-grid">
      <!-- Formulario de movimiento de stock -->
      <div class="auth-card">
        <h3><i class="fa-solid fa-arrows-up-down"></i> Registrar Entrada / Ajuste</h3>
        <form action="<?= BASE_URL ?>/inventario/movimiento" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">

          <div class="form-group">
            <label for="inv_prod">Peluche</label>
            <select name="producto_id" id="inv_prod" class="form-control" required>
              <?php foreach ($productos as $p): ?>
                <option value="<?= (int)$p['id'] ?>">
                  [<?= $this->e($p['codigo_sku']) ?>] <?= $this->e($p['nombre']) ?> (Actual: <?= (int)$p['stock_actual'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label for="inv_tipo">Tipo de operación</label>
              <select name="tipo" id="inv_tipo" class="form-control">
                <option value="entrada">Entrada (+ sumar stock)</option>
                <option value="salida">Salida (- restar stock)</option>
                <option value="ajuste">Ajuste (fijar stock exacto)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="inv_cant">Cantidad</label>
              <input type="number" name="cantidad" id="inv_cant" min="0" value="1" required class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label for="inv_motivo">Motivo / Guía</label>
            <input type="text" name="motivo" id="inv_motivo"
                   placeholder="Ej. Reposición proveedor Lima / Conteo físico" class="form-control">
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-floppy-disk"></i> Guardar Movimiento
          </button>
        </form>
      </div>

      <!-- Alta rápida de peluche (solo Admin) -->
      <?php if ($isAdmin): ?>
        <div class="auth-card">
          <h3><i class="fa-solid fa-plus-circle"></i> Nuevo Peluche en Catálogo</h3>
          <form action="<?= BASE_URL ?>/inventario/producto/crear" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-row-2">
              <div class="form-group">
                <label for="new_sku">Código SKU *</label>
                <input type="text" name="codigo_sku" id="new_sku" placeholder="MDE-OSO-099" required class="form-control">
              </div>
              <div class="form-group">
                <label for="new_cat">Categoría</label>
                <select name="categoria_id" id="new_cat" class="form-control">
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= $this->e($cat['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="new_nombre">Nombre del peluche *</label>
              <input type="text" name="nombre" id="new_nombre" placeholder="Osito Panda Kawaii 45cm" required class="form-control">
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="new_tamano">Tamaño</label>
                <select name="tamano" id="new_tamano" class="form-control">
                  <option value="Mini">Mini</option>
                  <option value="Mediano" selected>Mediano</option>
                  <option value="Grande">Grande</option>
                  <option value="Gigante">Gigante</option>
                </select>
              </div>
              <div class="form-group">
                <label for="new_color">Color</label>
                <input type="text" name="color" id="new_color" value="Pastel" class="form-control">
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="new_precio">Precio S/ *</label>
                <input type="number" name="precio" id="new_precio" step="0.50" min="1" placeholder="45.00" required class="form-control">
              </div>
              <div class="form-group">
                <label for="new_stock">Stock inicial</label>
                <input type="number" name="stock_inicial" id="new_stock" min="0" value="10" class="form-control">
              </div>
            </div>

            <button type="submit" class="btn btn-soft btn-block">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Crear Peluche y Stock
            </button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tabla de Stock Actual -->
    <div style="margin-top:2rem;">
      <h2>Existencias Actuales</h2>
      <div class="table-responsive-wrap" style="margin-top:0.8rem;">
        <table class="data-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Peluche</th>
              <th>Categoría</th>
              <th>Tamaño</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Mínimo</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $prod): ?>
              <?php $bajo = (int)$prod['stock_actual'] <= (int)$prod['stock_minimo']; ?>
              <tr>
                <td><strong><?= $this->e($prod['codigo_sku']) ?></strong></td>
                <td><?= $this->e($prod['nombre']) ?></td>
                <td><?= $this->e($prod['categoria_nombre']) ?></td>
                <td><?= $this->e($prod['tamano']) ?></td>
                <td>S/ <?= number_format((float)($prod['precio_oferta'] ?: $prod['precio']), 2) ?></td>
                <td><strong><?= (int)$prod['stock_actual'] ?> und.</strong></td>
                <td><?= (int)$prod['stock_minimo'] ?></td>
                <td>
                  <?php if ($bajo): ?>
                    <span class="stock-badge is-low"><i class="fa-solid fa-triangle-exclamation"></i> Reponer</span>
                  <?php else: ?>
                    <span class="stock-badge">Óptimo</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Kardex de Movimientos -->
    <div style="margin-top:2.2rem;">
      <h2>Historial Reciente de Movimientos (Kardex)</h2>
      <div class="table-responsive-wrap" style="margin-top:0.8rem;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Peluche</th>
              <th>Tipo</th>
              <th>Cant.</th>
              <th>Anterior → Nuevo</th>
              <th>Motivo</th>
              <th>Responsable</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($movimientos as $m): ?>
              <tr>
                <td><small><?= $this->e((string)$m['created_at']) ?></small></td>
                <td><strong><?= $this->e($m['producto_nombre']) ?></strong></td>
                <td><span class="stock-badge"><?= strtoupper($this->e($m['tipo'])) ?></span></td>
                <td><strong><?= (int)$m['cantidad'] ?></strong></td>
                <td><?= (int)$m['stock_anterior'] ?> → <strong><?= (int)$m['stock_nuevo'] ?></strong></td>
                <td><?= $this->e((string)($m['motivo'] ?? '')) ?></td>
                <td><?= $this->e($m['usuario_nombre']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>