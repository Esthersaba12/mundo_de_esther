<?php declare(strict_types=1); ?>
<section class="section-block">
  <div class="container">
    <div class="section-header">
      <span class="pill-badge"><i class="fa-solid fa-heart"></i> Lista de Deseos</span>
      <h1>Mis Peluches Favoritos</h1>
      <p>Guarda aquí los peluches que quieres regalar o coleccionar.</p>
    </div>

    <?php if (empty($favoritos)): ?>
      <div class="auth-card" style="text-align:center;max-width:520px;margin:0 auto;">
        <p>Todavía no has marcado peluches en tu lista de deseos.</p>
        <a href="<?= BASE_URL ?>/catalogo" class="btn btn-primary">Explorar Catálogo</a>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($favoritos as $fav): ?>
          <article class="product-card">
            <div class="product-card__media">
              <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($fav['slug']) ?>" class="plush-placeholder">
                <i class="fa-solid fa-heart"></i>
                <span><?= $this->e($fav['tamano']) ?></span>
              </a>
            </div>
            <div class="product-card__body">
              <span class="product-meta"><?= $this->e($fav['categoria_nombre']) ?> • <?= $this->e($fav['color']) ?></span>
              <h3 class="product-card__title"><?= $this->e($fav['nombre']) ?></h3>
              <div class="product-card__footer">
                <span class="price-current">S/ <?= number_format((float)($fav['precio_oferta'] ?: $fav['precio']), 2) ?></span>
                <a href="<?= BASE_URL ?>/producto?slug=<?= urlencode($fav['slug']) ?>" class="btn btn-primary btn-sm">Ver Peluche</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>