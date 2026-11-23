<?php
declare(strict_types=1);
$csrfToken = $_SESSION['csrf_token'] ?? '';
$currentUser = $_SESSION['user'] ?? null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
</head>
<body>
  <div class="promo-bar">
    <div class="container promo-bar__inner">
      <span><i class="fa-solid fa-sparkles"></i> ¡Envíos con cariño y envoltura especial en <strong>Mundo de Esther</strong>! Cupón: <b>ESTHER10</b></span>
      <span class="promo-bar__badge">v6 Punto de Venta POS</span>
    </div>
  </div>

  <header class="site-header">
    <div class="container header__inner">
      <button type="button" class="icon-btn hamburger-btn" id="hamburgerBtn" aria-label="Abrir menú">
        <i class="fa-solid fa-bars"></i>
      </button>

      <a href="<?= BASE_URL ?>/" class="brand-logo" aria-label="Mundo de Esther Inicio">
        <span class="brand-logo__text">Mundo de Esther</span>
        <i class="fa-solid fa-heart brand-logo__icon"></i>
      </a>

      <nav class="main-nav" id="mainNav">
        <a href="<?= BASE_URL ?>/#destacados" class="nav-link">Destacados</a>
        <a href="<?= BASE_URL ?>/catalogo" class="nav-link">Catálogo</a>
        <?php if ($currentUser): ?>
          <a href="<?= BASE_URL ?>/favoritos" class="nav-link">Favoritos</a>
        <?php endif; ?>
        <?php if ($currentUser && in_array($currentUser['rol'], ['staff', 'admin'], true)): ?>
          <a href="<?= BASE_URL ?>/pos" class="nav-link">Punto de Venta</a>
          <a href="<?= BASE_URL ?>/gestion/pedidos" class="nav-link">Pedidos</a>
        <?php endif; ?>
        <?php if ($currentUser && $currentUser['rol'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-link">Admin</a>
        <?php endif; ?>
      </nav>

      <div class="header-actions">
        <button type="button" class="icon-btn" id="themeToggle" title="Cambiar modo claro/oscuro" aria-label="Cambiar modo claro/oscuro">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <?php if ($currentUser): ?>
          <?php
            $panelLink = match ($currentUser['rol']) {
                'admin' => '/admin/dashboard',
                'staff' => '/staff/panel',
                default => '/cliente/perfil',
            };
          ?>
          <a href="<?= BASE_URL . $panelLink ?>" class="btn btn-soft btn-sm">
            <i class="fa-solid fa-user-check"></i>
            <span><?= htmlspecialchars(explode(' ', $currentUser['nombre'])[0], ENT_QUOTES, 'UTF-8') ?></span>
          </a>
          <form action="<?= BASE_URL ?>/logout" method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="icon-btn" title="Cerrar sesión" aria-label="Cerrar sesión">
              <i class="fa-solid fa-right-from-bracket"></i>
            </button>
          </form>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login" class="btn btn-soft btn-sm">
            <i class="fa-solid fa-user"></i>
            <span>Ingresar</span>
          </a>
        <?php endif; ?>

        <button type="button" class="btn btn-primary btn-sm open-cart-trigger">
          <i class="fa-solid fa-bag-shopping"></i>
          <span>Carrito (<span class="cart-count-badge">0</span>)</span>
        </button>
      </div>
    </div>
  </header>

  <?php if ($flash): ?>
    <div class="container" style="margin-top: 1rem;">
      <div class="alert-flash alert-flash--<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <span><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  <?php endif; ?>

  <main class="main-content">