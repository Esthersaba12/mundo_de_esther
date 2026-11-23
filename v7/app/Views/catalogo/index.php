<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-sparkles"></i> Colección Completa</span>
      <h1>Catálogo de Peluches</h1>
      <p>Filtra en vivo por categoría, tamaño, color, etiqueta o rango de precio.</p>
    </div>

    <!-- Barra de filtros en vivo (AJAX) -->
    <form id="catalogFilterForm" class="filter-panel">
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" name="q" id="filterQ" value="<?= $this->e($filtros['q']) ?>"
               placeholder="Buscar osos, conejos, unicornios o código SKU..." class="form-control">
      </div>

      <div class="filter-grid">
        <select name="categoria" id="filterCategoria" class="form-control">
          <option value="">Todas las categorías</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $this->e($cat['slug']) ?>" <?= $filtros['categoria'] === $cat['slug'] ? 'selected' : '' ?>>
              <?= $this->e($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="tamano" id="filterTamano" class="form-control">
          <option value="">Todos los tamaños</option>
          <?php foreach (['Mini', 'Mediano', 'Grande', 'Gigante'] as $tam): ?>
            <option value="<?= $tam ?>" <?= $filtros['tamano'] === $tam ? 'selected' : '' ?>><?= $tam ?></option>
          <?php endforeach; ?>
        </select>

        <select name="color" id="filterColor" class="form-control">
          <option value="">Todos los colores</option>
          <?php foreach ($colores as $col): ?>
            <option value="<?= $this->e($col) ?>" <?= $filtros['color'] === $col ? 'selected' : '' ?>>
              <?= $this->e($col) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="etiqueta" id="filterEtiqueta" class="form-control">
          <option value="">Todas las etiquetas</option>
          <option value="Nuevo" <?= $filtros['etiqueta'] === 'Nuevo' ? 'selected' : '' ?>>Nuevo</option>
          <option value="Oferta" <?= $filtros['etiqueta'] === 'Oferta' ? 'selected' : '' ?>>Oferta</option>
          <option value="Mas vendido" <?= $filtros['etiqueta'] === 'Mas vendido' ? 'selected' : '' ?>>Más vendido</option>
        </select>

        <input type="number" step="1" min="0" name="precio_min" id="filterMin"
               value="<?= $this->e($filtros['precio_min']) ?>" placeholder="Precio mín. S/" class="form-control">

        <input type="number" step="1" min="0" name="precio_max" id="filterMax"
               value="<?= $this->e($filtros['precio_max']) ?>" placeholder="Precio máx. S/" class="form-control">

        <select name="orden" id="filterOrden" class="form-control">
          <option value="destacados">Ordenar: Destacados</option>
          <option value="precio_asc">Precio: Menor a Mayor</option>
          <option value="precio_desc">Precio: Mayor a Menor</option>
          <option value="nuevos">Más Recientes</option>
        </select>

        <button type="button" id="resetFiltersBtn" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-rotate-left"></i> Limpiar
        </button>
      </div>
    </form>

    <div class="catalog-toolbar">
      <span id="catalogCount">Mostrando <strong><?= count($productos) ?></strong> peluches</span>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= BASE_URL ?>/favoritos" class="btn btn-soft btn-sm">
          <i class="fa-solid fa-heart"></i> Mi Lista de Deseos
        </a>
      <?php endif; ?>
    </div>

    <!-- Grid renderizado por servidor y actualizado vía AJAX -->
    <div class="products-grid" id="catalogResultsGrid">
      <?php foreach ($productos as $prod): ?>
        <?php
          $tieneOferta   = !empty($prod['precio_oferta']);
          $precioMostrar = $tieneOferta ? (float)$prod['precio_oferta'] : (float)$prod['precio'];
          $stockBajo     = (int)$prod['stock_actual'] <= (int)$prod['stock_minimo'];
          $esFav         = in_array((int)$prod['id'], $favoritosIds, true);
        ?>
        <article class="product-card">
          <div class="product-card__media">
            <?php if ($prod['etiqueta'] !== 'Ninguna'): ?>
              <span class="tag-badge tag-<?= strtolower(str_replace(' ', '-', $prod['etiqueta'])) ?>">
                <?= $this->e($prod['etiqueta']) ?>
              </span>
            <?php endif; ?>

            <button type="button"
                    class="fav-btn <?= $esFav ? 'is-fav' : '' ?>"
                    data-product-id="<?= (int)$prod['id'] ?>"
                    title="Guardar en favoritos"
                    aria-label="Guardar en favoritos">
              <i class="fa-<?= $esFav ? 'solid' : 'regular' ?> fa-heart"></i>
            </button>

            <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($prod['slug']) ?>" class="plush-placeholder">
              <i class="fa-solid fa-paw"></i>
              <span><?= $this->e($prod['tamano']) ?> • Ver detalle</span>
            </a>
          </div>

          <div class="product-card__body">
            <div class="product-meta">
              <span><?= $this->e($prod['categoria_nombre']) ?></span>
              <span>• <?= $this->e($prod['color']) ?></span>
              <span>• ★ <?= number_format((float)$prod['rating_promedio'], 1) ?></span>
            </div>
            <h3 class="product-card__title">
              <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($prod['slug']) ?>">
                <?= $this->e($prod['nombre']) ?>
              </a>
            </h3>
            <p class="product-card__desc"><?= $this->e($prod['descripcion']) ?></p>

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

            <div style="display:flex;gap:0.5rem;margin-top:0.85rem;">
              <button type="button"
                      class="btn btn-soft btn-sm add-to-cart-btn"
                      data-product-id="<?= (int)$prod['id'] ?>"
                      style="flex:1;">
                <i class="fa-solid fa-cart-plus"></i> Agregar
              </button>
              <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($prod['slug']) ?>"
                 class="btn btn-primary btn-sm"
                 style="flex:1;">
                <i class="fa-solid fa-eye"></i> Detalle
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>