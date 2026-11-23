<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container auth-wrapper">
    <div class="auth-card">
      <div class="auth-card__head">
        <span class="pill-badge"><i class="fa-solid fa-heart"></i> Bienvenido/a</span>
        <h1>Iniciar Sesión</h1>
        <p>Accede a tu cuenta de <strong>Mundo de Esther</strong> según tu rol.</p>
      </div>

      <form action="<?= BASE_URL ?>/login" method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com" class="form-control">
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" required placeholder="••••••••" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          <i class="fa-solid fa-right-to-bracket"></i> Ingresar
        </button>
      </form>

      <div class="demo-credentials">
        <strong>Accesos rápidos de prueba (Clave: <code>Esther2026!</code>)</strong>
        <ul>
          <li><b>Admin:</b> admin@mundodeesther.pe</li>
          <li><b>Staff:</b> staff@mundodeesther.pe</li>
          <li><b>Cliente:</b> cliente@mundodeesther.pe</li>
        </ul>
      </div>

      <p class="auth-switch">
        ¿Aún no tienes cuenta? <a href="<?= BASE_URL ?>/registro">Regístrate aquí</a>
      </p>
    </div>
  </div>
</section>