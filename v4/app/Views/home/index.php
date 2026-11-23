<?php
declare(strict_types=1);
?>

<!-- HERO PRINCIPAL + CARRUSEL DESTACADO -->
<section class="hero-section reveal">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="pill-badge">
        <i class="fa-solid fa-star"></i> Tienda Oficial de Peluches Tiernos
      </span>
      <h1 class="hero-title">
        Regala un abrazo suave con <span>Mundo de Esther</span>
      </h1>
      <p class="hero-subtitle">
        Osos, conejos, unicornios, personajes adorables y ediciones especiales elaborados con felpa ultra suave para sacar sonrisas inolvidables.
      </p>
      <div class="hero-cta">
        <a href="#catalogo" class="btn btn-primary">
          <i class="fa-solid fa-heart"></i> Explorar Peluches
        </a>
        <a href="#destacados" class="btn btn-outline">
          Ver Favoritos
        </a>
      </div>
      <div class="hero-stats">
        <div>
          <strong><?= count($productos) ?></strong>
          <span>Modelos Base</span>
        </div>
        <div>
          <strong><?= count($categorias) ?></strong>
          <span>Categorías</span>
        </div>
        <div>
          <strong>ACID / PDO</strong>
          <span>Stock Seguro</span>
        </div>
      </div>
    </div>

    <!-- Carrusel de peluches destacados -->
    <div class="hero-carousel-card">
      <div class="carousel-header">
        <span><i class="fa-solid fa-crown"></i> Destacados de la Semana</span>
        <div class="carousel-controls">
          <button type="button" class="icon-btn icon-btn--sm" id="prevSlide" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button type="button" class="icon-btn icon-btn--sm" id="nextSlide" aria-label="Siguiente">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <div class="carousel-track" id="featuredCarousel">
        <?php foreach ($destacados as $index => $item): ?>
          <?php
            $precioFinal = !empty($item['precio_oferta']) ? (float)$item['precio_oferta'] : (float)$item['precio'];
          ?>
          <article class="carousel-slide <?= $index === 0 ? 'is-active' : '' ?>">
            <div class="peluche-visual-box">
              <span class="tag-badge tag-<?= strtolower(str_replace(' ', '-', $item['etiqueta'])) ?>">
                <?= htmlspecialchars($item['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
              </span>
              <div class="plush-emblem">
                <i class="fa-solid fa-heart"></i>
              </div>
              <small class="sku-label">SKU: <?= htmlspecialchars($item['codigo_sku'], ENT_QUOTES, 'UTF-8') ?></small>
            </div>
            <div class="carousel-slide__body">
              <span class="cat-chip"><?= htmlspecialchars($item['categoria_nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              <h3><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($item['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
              <div class="price-row">
                <span class="price-current">S/ <?= number_format($precioFinal, 2) ?></span>
                <?php if (!empty($item['precio_oferta'])): ?>
                  <span class="price-old">S/ <?= number_format((float)$item['precio'], 2) ?></span>
                <?php endif; ?>
                <span class="stock-pill">Stock: <?= (int)$item['stock_actual'] ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORÍAS -->
<section id="categorias" class="section-block reveal">
  <div class="container">
    <div class="section-header">
      <h2>Nuestras Categorías</h2>
      <p>Encuentra el peluche ideal según su estilo, tamaño o momento especial.</p>
    </div>

    <div class="categories-grid">
      <?php foreach ($categorias as $cat): ?>
        <a href="#catalogo" class="category-card">
          <div class="category-card__icon">
            <i class="fa-solid <?= htmlspecialchars($cat['icono'] ?? 'fa-heart', ENT_QUOTES, 'UTF-8') ?>"></i>
          </div>
          <h3><?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars($cat['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CATÁLOGO INICIAL DESDE MYSQL -->
<section id="catalogo" class="section-block reveal">
  <div class="container">
    <div class="section-header">
      <h2>Catálogo Inicial (12 Peluches Seed)</h2>
      <p>Los filtros dinámicos por categoría, tamaño, color, precio y búsqueda AJAX se activan en la <strong>v3</strong>.</p>
    </div>

    <div class="products-grid">
      <?php foreach ($productos as $prod): ?>
        <?php
          $tieneOferta = !empty($prod['precio_oferta']);
          $precioMostrar = $tieneOferta ? (float)$prod['precio_oferta'] : (float)$prod['precio'];
          $stockBajo = (int)$prod['stock_actual'] <= (int)$prod['stock_minimo'];
        ?>
        <article class="product-card">
          <div class="product-card__media">
            <?php if ($prod['etiqueta'] !== 'Ninguna'): ?>
              <span class="tag-badge tag-<?= strtolower(str_replace(' ', '-', $prod['etiqueta'])) ?>">
                <?= htmlspecialchars($prod['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            <?php endif; ?>
            <div class="plush-placeholder" data-color="<?= htmlspecialchars($prod['color'], ENT_QUOTES, 'UTF-8') ?>">
              <i class="fa-solid fa-paw"></i>
              <span><?= htmlspecialchars($prod['tamano'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>

          <div class="product-card__body">
            <div class="product-meta">
              <span><?= htmlspecialchars($prod['categoria_nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              <span>• <?= htmlspecialchars($prod['color'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <h3 class="product-card__title"><?= htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="product-card__desc"><?= htmlspecialchars($prod['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="product-card__footer">
              <div class="price-box">
                <span class="price-current">S/ <?= number_format($precioMostrar, 2) ?></span>
                <?php if ($tieneOferta): ?>
                  <span class="price-old">S/ <?= number_format((float)$prod['precio'], 2) ?></span>
                <?php endif; ?>
              </div>
              <span class="stock-badge <?= $stockBajo ? 'is-low' : '' ?>">
                <?= (int)$prod['stock_actual'] ?> und.
              </span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ROADMAP DE VERSIONES -->
<section id="versiones" class="section-block reveal">
  <div class="container">
    <div class="section-header">
      <h2>Arquitectura Incremental v1 → v9</h2>
      <p>Construcción por etapas con control estricto de seguridad, roles y transacciones.</p>
    </div>
    <div class="roadmap-chips">
      <span class="chip is-active">v1 Base BD & MVC (Listo)</span>
      <span class="chip">v2 Login & Roles</span>
      <span class="chip">v3 Catálogo & Detalle</span>
      <span class="chip">v4 Carrito & Checkout</span>
      <span class="chip">v5 Yape/Plin & WhatsApp</span>
      <span class="chip">v6 POS</span>
      <span class="chip">v7 Despacho & Tickets 80mm</span>
      <span class="chip">v8 Inventario ACID</span>
      <span class="chip">v9 Dashboard Financiero</span>
    </div>
  </div>
</section>