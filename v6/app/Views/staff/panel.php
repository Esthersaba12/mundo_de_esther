<?php
declare(strict_types=1);
$user = $_SESSION['user'] ?? [];
?>
<section class="section-block">
  <div class="container">
    <div class="role-banner">
      <div>
        <span class="pill-badge"><i class="fa-solid fa-store"></i> Panel de Staff / Mostrador</span>
        <h1>¡Hola, <?= $this->e($user['nombre'] ?? 'Staff') ?>!</h1>
        <p style="color:var(--text-muted);margin:0;">
          Atiende ventas presenciales en el Punto de Venta, verifica pagos de Yape y actualiza estados de pedidos.
        </p>
      </div>
      <div class="detail-actions">
        <a href="<?= BASE_URL ?>/pos" class="btn btn-primary">
          <i class="fa-solid fa-cash-register"></i> Abrir Punto de Venta (POS)
        </a>
        <a href="<?= BASE_URL ?>/gestion/pedidos" class="btn btn-soft">
          <i class="fa-solid fa-list-check"></i> Gestionar Pedidos
        </a>
      </div>
    </div>
  </div>
</section>