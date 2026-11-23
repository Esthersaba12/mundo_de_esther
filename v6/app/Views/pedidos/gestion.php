<?php
declare(strict_types=1);
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-list-check"></i> Control de Estados y Pagos</span>
      <h1>Gestión de Pedidos</h1>
      <p>Verifica comprobantes Yape y actualiza estados: Pendiente, Pagado, Preparando, Enviado, Entregado, Cancelado.</p>
    </div>

    <div class="roadmap-chips" style="margin-bottom:1.5rem;">
      <a href="<?= BASE_URL ?>/gestion/pedidos" class="chip <?= empty($estadoActual) ? 'is-active' : '' ?>">Todos</a>
      <?php foreach ($estados as $st): ?>
        <a href="<?= BASE_URL ?>/gestion/pedidos?estado=<?= urlencode($st) ?>"
           class="chip <?= $estadoActual === $st ? 'is-active' : '' ?>">
          <?= $this->e($st) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="table-responsive-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Cliente</th>
            <th>Entrega</th>
            <th>Total</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $ped): ?>
            <tr>
              <td><strong><?= $this->e($ped['codigo_pedido']) ?></strong></td>
              <td>
                <?= $this->e($ped['nombre_cliente']) ?>
                <small style="display:block;color:var(--text-muted);"><?= $this->e($ped['telefono_cliente']) ?></small>
              </td>
              <td><?= $ped['tipo_entrega'] === 'recojo_tienda' ? 'Tienda' : 'Delivery' ?></td>
              <td><strong>S/ <?= number_format((float)$ped['total'], 2) ?></strong></td>
              <td>
                <?php if (!empty($ped['ultimo_metodo'])): ?>
                  <span class="stock-badge"><?= strtoupper($this->e($ped['ultimo_metodo'])) ?> (<?= $this->e($ped['estado_pago'] ?? 'pendiente') ?>)</span>
                <?php else: ?>
                  <small style="color:var(--text-muted);">Sin registro</small>
                <?php endif; ?>

                <?php if (!empty($ped['comprobante_ruta'])): ?>
                  <a href="<?= BASE_URL . '/' . ltrim($ped['comprobante_ruta'], '/') ?>"
                     target="_blank"
                     style="display:inline-block;margin-top:0.35rem;font-size:0.82rem;font-weight:600;color:#D65A9A;">
                    <i class="fa-solid fa-image"></i> Ver captura
                  </a>
                <?php endif; ?>
              </td>
              <td><span class="tag-badge" style="position:static;"><?= $this->e($ped['estado']) ?></span></td>
              <td>
                <form action="<?= BASE_URL ?>/gestion/pedidos/estado" method="POST" class="inline-status-form">
                  <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="pedido_id" value="<?= (int)$ped['id'] ?>">
                  <select name="estado" class="form-control" style="padding:0.4rem 0.6rem;width:auto;">
                    <?php foreach ($estados as $st): ?>
                      <option value="<?= $st ?>" <?= $ped['estado'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label style="font-size:0.78rem;display:inline-flex;align-items:center;gap:0.25rem;">
                    <input type="checkbox" name="verificar_pago" value="1"> Verificar pago
                  </label>
                  <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>