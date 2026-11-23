<?php
declare(strict_types=1);
$csrfToken = $_SESSION['csrf_token'] ?? '';
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
  <!-- Banner superior de promociones -->
  <div class="promo-bar">
    <div class="container promo-bar__inner">
      <span><i class="fa-solid fa-sparkles"></i> ¡Envíos con cariño y envoltura especial en <strong>Mundo de Esther</strong>! Usa el cupón <b>ESTHER10</b></span>
      <span class="promo-bar__badge">v1 Base MVC</span>
    </div>
  </div>

  <!-- Cabecera principal -->
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
        <a href="#destacados" class="nav-link">Destacados</a>
        <a href="#categorias" class="nav-link">Categorías</a>
        <a href="#catalogo" class="nav-link">Peluches</a>
        <a href="#versiones" class="nav-link">Hoja de Ruta</a>
      </nav>

      <div class="header-actions">
        <button type="button" class="icon-btn" id="themeToggle" title="Cambiar modo claro/oscuro" aria-label="Cambiar modo claro/oscuro">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <a href="#catalogo" class="btn btn-soft btn-sm">
          <i class="fa-solid fa-user"></i>
          <span>Ingresar (v2)</span>
        </a>
        <a href="#catalogo" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-bag-shopping"></i>
          <span>Carrito (0)</span>
        </a>
      </div>
    </div>
  </header>

  <main class="main-content">