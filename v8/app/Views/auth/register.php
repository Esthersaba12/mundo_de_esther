<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container auth-wrapper">
    <div class="auth-card">
      <div class="auth-card__head">
        <span class="pill-badge"><i class="fa-solid fa-star"></i> Nuevo Cliente</span>
        <h1>Crear Cuenta</h1>
        <p>Guarda tus peluches favoritos, revisa tus pedidos y recibe promociones.</p>
      </div>

      <form action="<?= BASE_URL ?>/registro" method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-group">
          <label for="nombre">Nombre completo *</label>
          <input type="text" id="nombre" name="nombre" required minlength="3" placeholder="Ej. Sofía Mendoza" class="form-control">
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico *</label>
          <input type="email" id="email" name="email" required placeholder="sofia@correo.com" class="form-control">
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="telefono">Teléfono / WhatsApp</label>
            <input type="tel" id="telefono" name="telefono" placeholder="951000000" class="form-control">
          </div>
          <div class="form-group">
            <label for="direccion">Dirección de entrega</label>
            <input type="text" id="direccion" name="direccion" placeholder="Jr. / Av. ..." class="form-control">
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="password">Contraseña (mín. 6) *</label>
            <input type="password" id="password" name="password" required minlength="6" class="form-control">
          </div>
          <div class="form-group">
            <label for="password_confirm">Confirmar contraseña *</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="6" class="form-control">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          <i class="fa-solid fa-user-plus"></i> Crear mi cuenta
        </button>
      </form>

      <p class="auth-switch">
        ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/login">Inicia sesión</a>
      </p>
    </div>
  </div>
</section>