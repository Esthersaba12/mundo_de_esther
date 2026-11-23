<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container">
    <div class="role-banner role-banner--cliente">
      <div>
        <span class="pill-badge">Rol: Cliente</span>
        <h1>Hola, <?= $this->e($usuario['nombre'] ?? '') ?></h1>
        <p><?= $this->e($usuario['email'] ?? '') ?> • Tel: <?= $this->e($usuario['telefono'] ?: 'No registrado') ?></p>
      </div>
      <a href="<?= BASE_URL ?>/" class="btn btn-primary">Ir al Catálogo</a>
    </div>

    <div class="categories-grid" style="margin-top:1.5rem;">
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-box-open"></i></div>
        <h3>Mis Pedidos</h3>
        <p>Seguimiento de compras y comprobantes Yape/Plin (disponible en v4–v5).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-heart"></i></div>
        <h3>Lista de Deseos</h3>
        <p>Tus peluches guardados para ocasiones especiales (disponible en v3).</p>
      </div>
      <div class="category-card">
        <div class="category-card__icon"><i class="fa-solid fa-location-dot"></i></div>
        <h3>Dirección</h3>
        <p><?= $this->e($usuario['direccion'] ?: 'Agrega tu dirección en el checkout.') ?></p>
      </div>
    </div>
  </div>
</section>