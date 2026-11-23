<?php
declare(strict_types=1);
$precioFinal = !empty($producto['precio_oferta']) ? (float)$producto['precio_oferta'] : (float)$producto['precio'];

$nombreProd = (string)($producto['nombre'] ?? 'Peluche');
$lowerProd  = mb_strtolower($nombreProd);
$colorProd  = mb_strtolower((string)($producto['color'] ?? ''));

$cuerpo = '#F8BBD0';
$oscuro = '#EC407A';
$acento = '#FFF8E1';
$fondo1 = '#FFF0F6';
$fondo2 = '#E3F2FD';
$tipo   = 'oso';

if (str_contains($lowerProd, 'conejo') || str_contains($lowerProd, 'conejit')) {
    $tipo = 'conejo';
    $cuerpo = '#E1BEE7';
    $oscuro = '#AB47BC';
    $fondo1 = '#F3E5F5';
    $fondo2 = '#FCE4EC';
} elseif (str_contains($lowerProd, 'unicorn')) {
    $tipo = 'unicornio';
    $cuerpo = '#F3E5F5';
    $oscuro = '#CE93D8';
    $acento = '#FFD54F';
    $fondo1 = '#E8EAF6';
    $fondo2 = '#FCE4EC';
} elseif (str_contains($lowerProd, 'capibar')) {
    $tipo = 'capibara';
    $cuerpo = '#D7CCC8';
    $oscuro = '#8D6E63';
    $fondo1 = '#EFEBE9';
    $fondo2 = '#FFF3E0';
} elseif (str_contains($lowerProd, 'panda')) {
    $tipo = 'panda';
    $cuerpo = '#FFFFFF';
    $oscuro = '#37474F';
    $fondo1 = '#ECEFF1';
    $fondo2 = '#F3E5F5';
} elseif (str_contains($lowerProd, 'gat') || str_contains($lowerProd, 'michi')) {
    $tipo = 'gato';
    $cuerpo = '#FFE0B2';
    $oscuro = '#FB8C00';
    $fondo1 = '#FFF3E0';
    $fondo2 = '#E1F5FE';
} elseif (str_contains($lowerProd, 'perr') || str_contains($lowerProd, 'cachorr')) {
    $tipo = 'perro';
    $cuerpo = '#FFCCBC';
    $oscuro = '#E64A19';
    $fondo1 = '#FBE9E7';
    $fondo2 = '#EDE7F6';
}

if (str_contains($colorProd, 'celeste') || str_contains($colorProd, 'azul')) {
    $cuerpo = '#B3E5FC';
    $oscuro = '#0288D1';
} elseif (str_contains($colorProd, 'lila') || str_contains($colorProd, 'morad')) {
    $cuerpo = '#E1BEE7';
    $oscuro = '#8E24AA';
} elseif (str_contains($colorProd, 'rosa') || str_contains($colorProd, 'rosad')) {
    $cuerpo = '#F8BBD0';
    $oscuro = '#D81B60';
} elseif (str_contains($colorProd, 'dorad') || str_contains($colorProd, 'amarill')) {
    $cuerpo = '#FFF59D';
    $oscuro = '#FBC02D';
}

$orejas = match ($tipo) {
    'conejo' => '<ellipse cx="128" cy="68" rx="15" ry="42" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><ellipse cx="172" cy="68" rx="15" ry="42" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><ellipse cx="128" cy="70" rx="7" ry="28" fill="#F48FB1"/><ellipse cx="172" cy="70" rx="7" ry="28" fill="#F48FB1"/>',
    'unicornio' => '<polygon points="150,32 138,76 162,76" fill="#FFD54F" stroke="#F57F17" stroke-width="2.5"/><circle cx="114" cy="86" r="18" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><circle cx="186" cy="86" r="18" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>',
    'gato' => '<polygon points="112,86 122,48 140,76" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><polygon points="188,86 178,48 160,76" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>',
    'panda' => '<circle cx="112" cy="82" r="18" fill="#37474F"/><circle cx="188" cy="82" r="18" fill="#37474F"/>',
    default => '<circle cx="112" cy="84" r="19" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><circle cx="188" cy="84" r="19" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/><circle cx="112" cy="84" r="10" fill="' . $oscuro . '" opacity="0.35"/><circle cx="188" cy="84" r="10" fill="' . $oscuro . '" opacity="0.35"/>',
};

$extra = $tipo === 'panda'
    ? '<ellipse cx="133" cy="108" rx="10" ry="13" transform="rotate(-15 133 108)" fill="#37474F"/><ellipse cx="167" cy="108" rx="10" ry="13" transform="rotate(15 167 108)" fill="#37474F"/>'
    : '';

$imgSrc = null;
foreach (['imagen_url', 'imagen', 'foto', 'imagen_principal'] as $col) {
    $val = trim((string)($producto[$col] ?? ''));
    if ($val === '') {
        continue;
    }
    $rel = ltrim($val, '/');
    if (str_starts_with($rel, 'public/')) {
        $rel = substr($rel, 7);
    }
    if (file_exists(PUBLIC_PATH . '/' . $rel)) {
        $imgSrc = BASE_URL . '/' . $rel;
        break;
    }
}

if ($imgSrc === null) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 220" width="100%" height="100%">'
        . '<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="' . $fondo1 . '"/><stop offset="100%" stop-color="' . $fondo2 . '"/></linearGradient></defs>'
        . '<rect width="300" height="220" fill="url(#bg)"/>'
        . '<circle cx="52" cy="46" r="18" fill="#FFF" opacity="0.65"/><circle cx="252" cy="52" r="24" fill="#FFF" opacity="0.55"/>'
        . $orejas
        . '<ellipse cx="150" cy="166" rx="44" ry="38" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>'
        . '<ellipse cx="150" cy="168" rx="26" ry="23" fill="' . $acento . '" opacity="0.85"/>'
        . '<ellipse cx="106" cy="154" rx="12" ry="20" transform="rotate(24 106 154)" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>'
        . '<ellipse cx="194" cy="154" rx="12" ry="20" transform="rotate(-24 194 154)" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>'
        . '<ellipse cx="150" cy="110" rx="46" ry="40" fill="' . $cuerpo . '" stroke="' . $oscuro . '" stroke-width="3"/>'
        . $extra
        . '<circle cx="133" cy="108" r="5.5" fill="#2B2438"/><circle cx="167" cy="108" r="5.5" fill="#2B2438"/>'
        . '<circle cx="131" cy="106" r="2" fill="#FFF"/><circle cx="165" cy="106" r="2" fill="#FFF"/>'
        . '<ellipse cx="121" cy="118" rx="7" ry="4.5" fill="#FF80AB" opacity="0.65"/><ellipse cx="179" cy="118" rx="7" ry="4.5" fill="#FF80AB" opacity="0.65"/>'
        . '<ellipse cx="150" cy="116" rx="6" ry="4.5" fill="#4E342E"/>'
        . '<path d="M142 123 Q150 130 158 123" fill="none" stroke="#4E342E" stroke-width="2.5" stroke-linecap="round"/>'
        . '</svg>';
    $imgSrc = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}
?>
<section class="section-block">
  <div class="container">
    <nav class="breadcrumb-nav">
      <a href="<?= BASE_URL ?>/">Inicio</a> /
      <a href="<?= BASE_URL ?>/catalogo">Catálogo</a> /
      <span><?= $this->e($nombreProd) ?></span>
    </nav>

    <div class="product-detail-grid">
      <div>
        <?php if (!empty($producto['etiqueta']) && $producto['etiqueta'] !== 'Ninguna'): ?>
          <?php $slugTag = strtolower(str_replace(' ', '-', (string)$producto['etiqueta'])); ?>
          <span class="tag-badge tag-<?= $this->e($slugTag) ?>" style="position:static;display:inline-block;margin-bottom:0.6rem;">
            <?= $this->e((string)$producto['etiqueta']) ?>
          </span>
        <?php endif; ?>

        <div class="zoom-stage" id="zoomStage" style="cursor:zoom-in;">
          <div class="zoom-plush-art" id="zoomTarget" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <img id="detailMainImg"
                 src="<?= $this->e($imgSrc) ?>"
                 alt="<?= $this->e($nombreProd) ?>"
                 style="width:100%;height:100%;object-fit:contain;border-radius:16px;">
          </div>
        </div>
      </div>

      <div>
        <div class="product-meta" style="margin-bottom:0.4rem;">
          <span><?= $this->e((string)($producto['categoria_nombre'] ?? 'Peluches')) ?></span>
          <span>• SKU: <?= $this->e((string)$producto['codigo_sku']) ?></span>
          <span>• ★ <?= number_format((float)($producto['rating_promedio'] ?? 5), 1) ?></span>
        </div>

        <h1><?= $this->e($nombreProd) ?></h1>

        <div class="price-box" style="margin:0.75rem 0;">
          <span class="detail-price">S/ <?= number_format($precioFinal, 2) ?></span>
          <?php if (!empty($producto['precio_oferta'])): ?>
            <span class="price-old">S/ <?= number_format((float)$producto['precio'], 2) ?></span>
          <?php endif; ?>
          <span class="stock-badge" style="margin-left:0.5rem;"><?= (int)($producto['stock_actual'] ?? 0) ?> disponibles</span>
        </div>

        <p style="color:var(--text-muted);"><?= $this->e((string)($producto['descripcion'] ?? '')) ?></p>

        <ul class="detail-specs">
          <li><strong>Tamaño:</strong> <?= $this->e((string)($producto['tamano'] ?? 'Mediano')) ?></li>
          <li><strong>Color:</strong> <?= $this->e((string)($producto['color'] ?? 'Pastel')) ?></li>
          <li><strong>Material:</strong> Felpa hipoalergénica suave de alta calidad</li>
        </ul>

        <div class="form-row-2" style="margin-bottom:0.85rem;">
          <div class="form-group" style="margin-bottom:0;">
            <label for="detailQty">Cantidad</label>
            <input type="number" id="detailQty" min="1" max="<?= max(1, (int)($producto['stock_actual'] ?? 10)) ?>" value="1" class="form-control">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label for="detailDedicatoria">Dedicatoria (opcional)</label>
            <input type="text" id="detailDedicatoria" placeholder="Ej. ¡Feliz cumpleaños!" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-card">
            <input type="checkbox" id="detailEnvoltura" value="1">
            <span>Agregar envoltura de regalo especial (+S/ <?= number_format((float)COSTO_ENVOLTURA_REGALO, 2) ?>)</span>
          </label>
        </div>

        <div class="detail-actions">
          <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="<?= (int)$producto['id'] ?>" style="flex:1;">
            <i class="fa-solid fa-cart-plus"></i> Agregar al Carrito
          </button>
          <button type="button" class="icon-btn fav-btn-inline <?= !empty($esFavorito) ? 'is-fav' : '' ?>" data-product-id="<?= (int)$producto['id'] ?>" title="Guardar en favoritos">
            <i class="fa-<?= !empty($esFavorito) ? 'solid' : 'regular' ?> fa-heart"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="reviews-section">
      <h3>Reseñas de Clientes</h3>

      <?php if (!empty($_SESSION['user'])): ?>
        <form action="<?= BASE_URL ?>/producto/resena" method="POST" class="review-form">
          <input type="hidden" name="csrf_token" value="<?= $this->e((string)($_SESSION['csrf_token'] ?? '')) ?>">
          <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
          <input type="hidden" name="slug" value="<?= $this->e((string)$producto['slug']) ?>">

          <div class="form-row-2">
            <div class="form-group">
              <label for="calificacion">Calificación</label>
              <select name="calificacion" id="calificacion" class="form-control">
                <option value="5">★★★★★ (5 estrellas)</option>
                <option value="4">★★★★☆ (4 estrellas)</option>
                <option value="3">★★★☆☆ (3 estrellas)</option>
                <option value="2">★★☆☆☆ (2 estrellas)</option>
                <option value="1">★☆☆☆☆ (1 estrella)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="comentario">Tu opinión</label>
              <input type="text" name="comentario" id="comentario" placeholder="¿Qué te pareció el peluche?" required class="form-control">
            </div>
          </div>

          <button type="submit" class="btn btn-soft btn-sm">
            <i class="fa-solid fa-paper-plane"></i> Publicar Reseña
          </button>
        </form>
      <?php endif; ?>

      <div class="reviews-list">
        <?php if (empty($resenas)): ?>
          <p style="color:var(--text-muted);">Aún no hay reseñas para este peluche. ¡Sé el primero en opinar!</p>
        <?php else: ?>
          <?php foreach ($resenas as $rev): ?>
            <div class="review-card">
              <div class="review-card__head">
                <strong><?= $this->e((string)($rev['usuario_nombre'] ?? 'Cliente')) ?></strong>
                <span class="stars-display"><?= str_repeat('★', max(1, min(5, (int)($rev['calificacion'] ?? 5)))) ?></span>
              </div>
              <p style="margin:0;font-size:0.88rem;"><?= $this->e((string)($rev['comentario'] ?? '')) ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>