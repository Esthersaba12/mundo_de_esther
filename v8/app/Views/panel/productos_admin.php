<?php
declare(strict_types=1);
$edit = $productoEditar ?? null;
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-tags"></i> Catálogo y Ofertas</span>
      <h1>Administrar Peluches</h1>
      <p>Edita precios, precios de oferta, etiquetas, disponibilidad y sube fotos reales de tus peluches.</p>
    </div>

    <?php if ($edit): ?>
      <div class="auth-card" style="max-width:740px;margin:0 auto 2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
          <h3 style="margin:0;">Editando: <?= $this->e((string)$edit['nombre']) ?></h3>
          <a href="<?= BASE_URL ?>/admin/productos" class="btn btn-outline btn-sm">Cancelar</a>
        </div>

        <form action="<?= BASE_URL ?>/admin/productos/editar" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">

          <div class="form-row-2">
            <div class="form-group">
              <label for="ed_sku">Código SKU *</label>
              <input type="text" name="codigo_sku" id="ed_sku" value="<?= $this->e((string)$edit['codigo_sku']) ?>" required class="form-control">
            </div>
            <div class="form-group">
              <label for="ed_cat">Categoría</label>
              <select name="categoria_id" id="ed_cat" class="form-control">
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>" <?= (int)$edit['categoria_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= $this->e((string)$cat['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="ed_nombre">Nombre *</label>
            <input type="text" name="nombre" id="ed_nombre" value="<?= $this->e((string)$edit['nombre']) ?>" required class="form-control">
          </div>

          <div class="form-group">
            <label for="ed_desc">Descripción</label>
            <textarea name="descripcion" id="ed_desc" rows="2" class="form-control"><?= $this->e((string)($edit['descripcion'] ?? '')) ?></textarea>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label for="ed_precio">Precio normal S/ *</label>
              <input type="number" name="precio" id="ed_precio" step="0.50" min="1" value="<?= number_format((float)$edit['precio'], 2, '.', '') ?>" required class="form-control">
            </div>
            <div class="form-group">
              <label for="ed_oferta">Precio oferta S/ (vacío si no hay)</label>
              <input type="number" name="precio_oferta" id="ed_oferta" step="0.50" min="0" value="<?= $edit['precio_oferta'] !== null ? number_format((float)$edit['precio_oferta'], 2, '.', '') : '' ?>" class="form-control">
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label for="ed_tamano">Tamaño</label>
              <select name="tamano" id="ed_tamano" class="form-control">
                <?php foreach (['Mini', 'Mediano', 'Grande', 'Gigante'] as $t): ?>
                  <option value="<?= $t ?>" <?= ($edit['tamano'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="ed_etiqueta">Etiqueta</label>
              <select name="etiqueta" id="ed_etiqueta" class="form-control">
                <?php foreach (['Ninguna', 'Nuevo', 'Oferta', 'Mas vendido'] as $et): ?>
                  <option value="<?= $et ?>" <?= ($edit['etiqueta'] ?? '') === $et ? 'selected' : '' ?>><?= $et ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label for="ed_color">Color</label>
              <input type="text" name="color" id="ed_color" value="<?= $this->e((string)($edit['color'] ?? 'Pastel')) ?>" class="form-control">
            </div>
            <div class="form-group">
              <label for="ed_img">Foto del peluche (JPG, PNG, WEBP — máx. 4MB)</label>
              <input type="file" name="imagen" id="ed_img" accept="image/jpeg,image/png,image/webp" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label style="display:inline-flex;align-items:center;gap:0.45rem;">
              <input type="checkbox" name="activo" value="1" <?= !empty($edit['activo']) ? 'checked' : '' ?>>
              Peluche visible y activo en el catálogo
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <i class="fa-solid fa-check"></i> Guardar Cambios del Peluche
          </button>
        </form>
      </div>
    <?php endif; ?>

    <div class="table-responsive-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Peluche</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($productos as $p): ?>
            <tr>
              <td><strong><?= $this->e((string)$p['codigo_sku']) ?></strong></td>
              <td>
                <strong><?= $this->e((string)$p['nombre']) ?></strong>
                <small style="display:block;color:var(--text-muted);"><?= $this->e((string)$p['tamano']) ?> • <?= $this->e((string)$p['color']) ?></small>
              </td>
              <td><?= $this->e((string)$p['categoria_nombre']) ?></td>
              <td>
                <?php if ($p['precio_oferta'] !== null && (float)$p['precio_oferta'] > 0): ?>
                  <strong>S/ <?= number_format((float)$p['precio_oferta'], 2) ?></strong>
                  <small style="text-decoration:line-through;color:var(--text-muted);">S/ <?= number_format((float)$p['precio'], 2) ?></small>
                <?php else: ?>
                  <strong>S/ <?= number_format((float)$p['precio'], 2) ?></strong>
                <?php endif; ?>
              </td>
              <td><?= (int)$p['stock_actual'] ?> und.</td>
              <td>
                <span class="stock-badge"><?= !empty($p['activo']) ? 'Activo' : 'Oculto' ?></span>
              </td>
              <td>
                <a href="<?= BASE_URL ?>/admin/productos?editar=<?= (int)$p['id'] ?>" class="btn btn-soft btn-sm">
                  <i class="fa-solid fa-pen"></i> Editar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>