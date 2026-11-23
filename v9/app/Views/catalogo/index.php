<?php
declare(strict_types=1);
$favSet = array_flip($favoritosIds ?? []);

$fotoPeluchePorNombre = static function (array $p): string {
    foreach (['imagen_url', 'imagen', 'foto', 'imagen_principal'] as $col) {
        $val = trim((string)($p[$col] ?? ''));
        if ($val === '') {
            continue;
        }
        $rel = ltrim($val, '/');
        if (str_starts_with($rel, 'public/')) {
            $rel = substr($rel, 7);
        }
        if (file_exists(PUBLIC_PATH . '/' . $rel)) {
            return BASE_URL . '/' . $rel;
        }
    }

    $lower = mb_strtolower((string)($p['nombre'] ?? ''));
    $archivoSubido = 'assets/uploads/productos/peluche_1795441474_f539be36.webp';

    if (str_contains($lower, 'osito miel') && file_exists(PUBLIC_PATH . '/' . $archivoSubido)) {
        return BASE_URL . '/' . $archivoSubido;
    }

    $candidato = 'assets/uploads/productos/oso-miel.jpg';
    if (str_contains($lower, 'conejo') || str_contains($lower, 'conejit')) {
        $candidato = 'assets/uploads/productos/conejo-lila.jpg';
    } elseif (str_contains($lower, 'unicorn')) {
        $candidato = 'assets/uploads/productos/unicornio-pastel.jpg';
    } elseif (str_contains($lower, 'capibar')) {
        $candidato = 'assets/uploads/productos/capibara.jpg';
    } elseif (str_contains($lower, 'panda')) {
        $candidato = 'assets/uploads/productos/panda.jpg';
    }

    if (file_exists(PUBLIC_PATH . '/' . $candidato)) {
        return BASE_URL . '/' . $candidato;
    }
    if (file_exists(PUBLIC_PATH . '/' . $archivoSubido)) {
        return BASE_URL . '/' . $archivoSubido;
    }

    return BASE_URL . '/' . $candidato;
};
?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-sparkles"></i> Colección Completa</span>
      <h1>Catálogo de Peluches</h1>
      <p>Filtra en tiempo real por categoría, tamaño, color, precio o etiqueta y encuentra el regalo ideal.</p>
    </div>

    <form id="catalogFilterForm" class="filter-panel" method="GET" action="<?= BASE_URL ?>/catalogo">
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" name="q" value="<?= $this->e((string)($filtros['q'] ?? '')) ?>"
               placeholder="Buscar por nombre, SKU o ternura (ej. Unicornio, Capibara, Gigante)..." class="form-control">
      </div>

      <div class="filter-grid">
        <select name="categoria" class="form-control">
          <option value="">Todas las categorías</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $this->e((string)$cat['slug']) ?>" <?= ($filtros['categoria'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
              <?= $this->e((string)$cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="tamano" class="form-control">
          <option value="">Todos los tamaños</option>
          <?php foreach (['Mini', 'Mediano', 'Grande', 'Gigante'] as $t): ?>
            <option value="<?= $t ?>" <?= ($filtros['tamano'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>

        <select name="color" class="form-control">
          <option value="">Todos los colores</option>
          <?php foreach ($colores as $col): ?>
            <option value="<?= $this->e((string)$col) ?>" <?= ($filtros['color'] ?? '') === $col ? 'selected' : '' ?>>
              <?= $this->e((string)$col) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="etiqueta" class="form-control">
          <option value="">Todas las etiquetas</option>
          <?php foreach (['Nuevo', 'Oferta', 'Mas vendido'] as $et): ?>
            <option value="<?= $et ?>" <?= ($filtros['etiqueta'] ?? '') === $et ? 'selected' : '' ?>><?= $et ?></option>
          <?php endforeach; ?>
        </select>

        <input type="number" name="precio_max" step="1" min="0"
               value="<?= $this->e((string)($filtros['precio_max'] ?? '')) ?>"
               placeholder="Precio máx. S/" class="form-control">

        <select name="orden" class="form-control">
          <option value="destacados" <?= ($filtros['orden'] ?? '') === 'destacados' ? 'selected' : '' ?>>Destacados</option>
          <option value="precio_asc" <?= ($filtros['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Menor precio</option>
          <option value="precio_desc" <?= ($filtros['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Mayor precio</option>
          <option value="rating" <?= ($filtros['orden'] ?? '') === 'rating' ? 'selected' : '' ?>>Mejor valorados</option>
        </select>
      </div>
    </form>

    <div class="catalog-toolbar">
      <span id="catalogCount">Mostrando <strong><?= count($productos) ?></strong> peluches</span>
      <button type="button" id="resetFiltersBtn" class="btn btn-outline btn-sm">Limpiar filtros</button>
    </div>

    <div class="products-grid" id="catalogResultsGrid">
      <?php foreach ($productos as $p): ?>
        <?php
          $precio = !empty($p['precio_oferta']) ? (float)$p['precio_oferta'] : (float)$p['precio'];
          $esFav = isset($favSet[(int)$p['id']]);
          $imgSrc = $fotoPeluchePorNombre($p);
        ?>
        <article class="product-card">
          <div class="product-card__media">
            <?php if (!empty($p['etiqueta']) && $p['etiqueta'] !== 'Ninguna'): ?>
              <?php $slugTag = strtolower(str_replace(' ', '-', (string)$p['etiqueta'])); ?>
              <span class="tag-badge tag-<?= $this->e($slugTag) ?>"><?= $this->e((string)$p['etiqueta']) ?></span>
            <?php endif; ?>

            <button type="button" class="fav-btn <?= $esFav ? 'is-fav' : '' ?>" data-product-id="<?= (int)$p['id'] ?>" aria-label="Favorito">
              <i class="fa-<?= $esFav ? 'solid' : 'regular' ?> fa-heart"></i>
            </button>

            <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode((string)$p['slug']) ?>" class="plush-placeholder" style="width:100%;height:100%;">
              <img src="<?= $this->e($imgSrc) ?>"
                   alt="<?= $this->e((string)$p['nombre']) ?>"
                   style="width:100%;height:100%;object-fit:cover;">
            </a>
          </div>

          <div class="product-card__body">
            <div class="product-meta">
              <span><?= $this->e((string)$p['categoria_nombre']) ?></span>
              <span>• <?= $this->e((string)$p['color']) ?></span>
              <span>• ★ <?= number_format((float)($p['rating_promedio'] ?? 5), 1) ?></span>
            </div>
            <h3 class="product-card__title">
              <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode((string)$p['slug']) ?>"><?= $this->e((string)$p['nombre']) ?></a>
            </h3>
            <p class="product-card__desc"><?= $this->e((string)$p['descripcion']) ?></p>
            <div class="product-card__footer">
              <div class="price-box">
                <span class="price-current">S/ <?= number_format($precio, 2) ?></span>
                <?php if (!empty($p['precio_oferta'])): ?>
                  <span class="price-old">S/ <?= number_format((float)$p['precio'], 2) ?></span>
                <?php endif; ?>
              </div>
              <span class="stock-badge"><?= (int)$p['stock_actual'] ?> und.</span>
            </div>
            <div style="display:flex;gap:0.5rem;margin-top:0.85rem;">
              <button type="button" class="btn btn-soft btn-sm add-to-cart-btn" data-product-id="<?= (int)$p['id'] ?>" style="flex:1;">
                <i class="fa-solid fa-cart-plus"></i> Agregar
              </button>
              <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode((string)$p['slug']) ?>" class="btn btn-primary btn-sm" style="flex:1;">
                <i class="fa-solid fa-eye"></i> Detalle
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>