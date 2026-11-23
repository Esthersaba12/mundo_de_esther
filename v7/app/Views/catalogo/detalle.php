<?php
declare(strict_types=1);
$tieneOferta   = !empty($producto['precio_oferta']);
$precioMostrar = $tieneOferta ? (float)$producto['precio_oferta'] : (float)$producto['precio'];
$imagenes      = $producto['imagenes'] ?: [['ruta_imagen' => 'vista-principal', 'es_principal' => 1]];
?>
<section class="section-block">
  <div class="container">
    <nav class="breadcrumb-nav">
      <a href="<?= BASE_URL ?>/">Inicio</a> /
      <a href="<?= BASE_URL ?>/catalogo">Catálogo</a> /
      <span><?= $this->e($producto['nombre']) ?></span>
    </nav>

    <div class="product-detail-grid">
      <!-- Galería con lupa / zoom interactivo -->
      <div class="detail-gallery">
        <div class="zoom-stage" id="zoomStage">
          <?php if ($producto['etiqueta'] !== 'Ninguna'): ?>
            <span class="tag-badge tag-<?= strtolower(str_replace(' ', '-', $producto['etiqueta'])) ?>">
              <?= $this->e($producto['etiqueta']) ?>
            </span>
          <?php endif; ?>
          <div class="zoom-plush-art" id="zoomTarget">
            <i class="fa-solid fa-heart"></i>
            <strong><?= $this->e($producto['nombre']) ?></strong>
            <small id="zoomCaption">Vista 1 • Pasa el cursor para hacer zoom</small>
          </div>
        </div>

        <div class="gallery-thumbs">
          <?php foreach ($imagenes as $idx => $img): ?>
            <button type="button"
                    class="thumb-btn <?= $idx === 0 ? 'is-active' : '' ?>"
                    data-label="Vista <?= $idx + 1 ?> (<?= $this->e(basename($img['ruta_imagen'])) ?>)">
              <i class="fa-solid fa-paw"></i>
              <span>Vista <?= $idx + 1 ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Información del peluche -->
      <div class="detail-info">
        <span class="pill-badge"><?= $this->e($producto['categoria_nombre']) ?> • SKU: <?= $this->e($producto['codigo_sku']) ?></span>
        <h1><?= $this->e($producto['nombre']) ?></h1>

        <div class="detail-price-box">
          <span class="detail-price">S/ <?= number_format($precioMostrar, 2) ?></span>
          <?php if ($tieneOferta): ?>
            <span class="price-old">S/ <?= number_format((float)$producto['precio'], 2) ?></span>
          <?php endif; ?>
        </div>

        <p class="detail-desc"><?= $this->e($producto['descripcion']) ?></p>

        <ul class="detail-specs">
          <li><strong>Tamaño:</strong> <?= $this->e($producto['tamano']) ?></li>
          <li><strong>Color:</strong> <?= $this->e($producto['color']) ?></li>
          <li><strong>Stock disponible:</strong>
            <span class="stock-badge"><?= (int)$producto['stock_actual'] ?> unidades listas</span>
          </li>
        </ul>

        <div class="form-row-2" style="margin-top:1rem;">
          <div class="form-group">
            <label for="detailQty">Cantidad</label>
            <input type="number" id="detailQty" min="1" max="<?= max(1, (int)$producto['stock_actual']) ?>" value="1" class="form-control">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;">
            <label class="checkbox-card" style="width:100%;padding:0.65rem 0.85rem;">
              <input type="checkbox" id="detailEnvoltura" value="1">
              <span><strong>Envoltura de regalo</strong> (+S/ <?= number_format((float)COSTO_ENVOLTURA_REGALO, 2) ?>)</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label for="detailDedicatoria">Dedicatoria personalizada (opcional)</label>
          <input type="text" id="detailDedicatoria" maxlength="200"
                 placeholder="Ej. ¡Feliz cumpleaños, te quiero mucho!" class="form-control">
        </div>

        <div class="detail-actions">
          <button type="button"
                  class="btn btn-primary add-to-cart-btn"
                  data-product-id="<?= (int)$producto['id'] ?>">
            <i class="fa-solid fa-bag-shopping"></i> Añadir al Carrito
          </button>
          <button type="button"
                  class="btn btn-outline fav-btn-inline <?= $esFavorito ? 'is-fav' : '' ?>"
                  data-product-id="<?= (int)$producto['id'] ?>">
            <i class="fa-<?= $esFavorito ? 'solid' : 'regular' ?> fa-heart"></i>
            <span><?= $esFavorito ? 'En Favoritos' : 'Guardar en Deseos' ?></span>
          </button>
        </div>
      </div>
    </div>

    <!-- Sección de Reseñas con Estrellas -->
    <div class="reviews-section">
      <h2>Opiniones de Clientes (<?= count($resenas) ?>)</h2>

      <?php if (!empty($_SESSION['user'])): ?>
        <form action="<?= BASE_URL ?>/producto/resena" method="POST" class="review-form">
          <input type="hidden" name="csrf_token" value="<?= $this->e($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
          <input type="hidden" name="slug" value="<?= $this->e($producto['slug']) ?>">

          <div class="form-row-2">
            <div class="form-group">
              <label for="calificacion">Tu calificación</label>
              <select name="calificacion" id="calificacion" class="form-control">
                <option value="5">★★★★★ (5 - Excelente)</option>
                <option value="4">★★★★☆ (4 - Muy bueno)</option>
                <option value="3">★★★☆☆ (3 - Bueno)</option>
                <option value="2">★★☆☆☆ (2 - Regular)</option>
                <option value="1">★☆☆☆☆ (1 - Bajo)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="comentario">Tu comentario</label>
              <input type="text" name="comentario" id="comentario" required minlength="4"
                     placeholder="Cuéntanos qué te pareció su suavidad y acabado..." class="form-control">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Publicar Reseña</button>
        </form>
      <?php else: ?>
        <p class="auth-switch" style="text-align:left;margin-bottom:1rem;">
          <a href="<?= BASE_URL ?>/login">Inicia sesión</a> para dejar una reseña con estrellas.
        </p>
      <?php endif; ?>

      <div class="reviews-list">
        <?php if (empty($resenas)): ?>
          <p>Aún no hay reseñas para este peluche. ¡Sé el primero en opinar!</p>
        <?php else: ?>
          <?php foreach ($resenas as $rev): ?>
            <article class="review-card">
              <div class="review-card__head">
                <strong><?= $this->e($rev['autor']) ?></strong>
                <span class="stars-display"><?= str_repeat('★', (int)$rev['calificacion']) . str_repeat('☆', 5 - (int)$rev['calificacion']) ?></span>
              </div>
              <p><?= $this->e($rev['comentario']) ?></p>
              <small><?= $this->e($rev['created_at']) ?></small>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Productos Relacionados -->
    <?php if (!empty($relacionados)): ?>
      <div style="margin-top:2.5rem;">
        <h2>También te puede encantar</h2>
        <div class="products-grid" style="margin-top:1rem;">
          <?php foreach ($relacionados as $rel): ?>
            <article class="product-card">
              <div class="product-card__media">
                <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($rel['slug']) ?>" class="plush-placeholder">
                  <i class="fa-solid fa-paw"></i>
                  <span><?= $this->e($rel['tamano']) ?></span>
                </a>
              </div>
              <div class="product-card__body">
                <h3 class="product-card__title">
                  <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($rel['slug']) ?>"><?= $this->e($rel['nombre']) ?></a>
                </h3>
                <div class="product-card__footer">
                  <span class="price-current">S/ <?= number_format((float)($rel['precio_oferta'] ?: $rel['precio']), 2) ?></span>
                  <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($rel['slug']) ?>" class="btn btn-soft btn-sm">Ver</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>