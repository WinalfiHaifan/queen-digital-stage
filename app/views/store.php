<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
  <title>Store | Queen Digital Stage</title>
  <meta name="description" content="Queen-inspired store page with product cards and cart interaction.">
  <link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-store">
  <a class="skip-link" href="#main-content">Skip to content</a>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.php" aria-label="Queen Digital Stage Home"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
      <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false">☰</button>
      <nav class="site-nav" aria-label="Main navigation">
        <a class="" href="index.php">Home</a>
<a class="" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a class="" href="history.php">History</a>
<a class="" href="gallery.php">Media</a>
<a class="" href="fan-zone.php">Fan Zone</a>
<a class="active" href="store.php">Store</a>
      </nav>
    </div>
  </header>
  <main id="main-content">

    <section class="page-hero store-hero"><div class="container"><p class="eyebrow">Store</p><h1>Queen-inspired merchandise.</h1><p class="lead">Browse apparel, posters, and collectible-style items inspired by Queen’s visual identity and album archive.</p></div></section>
    <section class="section">
      <div class="container">
        <div class="section-heading split"><div><p class="eyebrow">Merchandise</p><h2>Featured merchandise.</h2></div>
        <p class="cart-note"><button type="button" class="cart-link" data-open-cart>Cart</button> <span id="cart-count">0</span> <span class="cart-divider">•</span> <span id="cart-total">0.00</span></p></div>
        <div class="gallery-toolbar" aria-label="Store filters" role="group">
<button class="filter-btn product-filter active" type="button" data-filter="all" aria-pressed="true">All</button>
<?php foreach ($categories as $category): ?>
<button class="filter-btn product-filter" type="button" data-filter="<?= e($category['category_id']) ?>" aria-pressed="false" title="<?= e($category['description']) ?>"><?= e($category['category_name']) ?></button>
<?php endforeach; ?>
</div>
        <div class="product-grid">
<?php foreach ($catalog as $product): ?>
    <article class="product-card" data-category="<?= e($product['category_id']) ?>">
        <?php coverImage($product['image_url'], $product['product_name']); ?>
        <div class="product-card-body">
            <p class="product-category"><?= e($product['category_name']) ?></p>
            <h3><?= e($product['product_name']) ?></h3>
            <p class="price"><?= e(money($product)) ?></p>
            <p class="product-stock <?= $product['stock'] > 0 ? '' : 'stock-empty' ?>"><?= $product['stock'] > 0 ? e($product['stock']) . ' in stock' : 'Out of Stock' ?></p>
            <a class="btn btn-primary select-product" href="store.php?product=<?= e($product['product_id']) ?>#featured-product">View Product</a>
        </div>
    </article>
<?php endforeach; ?>
<?php if (!$catalog): ?><p class="empty-state">No merchandise is available right now.</p><?php endif; ?>
</div>
      <p class="empty-state" id="store-empty" hidden>No products in this category.</p></div>
    </section>
    <section class="section" id="featured-product">
<div class="container">
    <div class="section-heading"><p class="eyebrow">Featured Product</p><h2><?= e($selected['product_name'] ?? 'Merchandise details') ?></h2></div>
    <?php if ($selected): ?>
    <div class="featured-product">
        <div class="featured-image"><?php coverImage($selected['image_url'], $selected['product_name']); ?></div>
        <div class="featured-content" data-featured-product="<?= e($selected['product_id']) ?>">
            <h3><?= e($selected['product_name']) ?></h3>
            <p class="price"><?= e(money($selected)) ?></p>
            <p><?= e($selected['description']) ?></p>
            <dl class="product-facts"><div><dt>Availability</dt><dd class="<?= $selected['stock'] > 0 ? '' : 'stock-empty' ?>"><?= $selected['stock'] > 0 ? e($selected['stock']) . ' in stock' : 'Out of Stock' ?></dd></div><div><dt>Weight per item</dt><dd><?= e(rtrim(rtrim((string) $selected['weight_gram'], '0'), '.')) ?> g</dd></div></dl>
            <?php if ($sizes = productSizes($selected)): ?>
                <label class="quantity-field" for="featured-size">Size</label>
                <select class="input size-input" id="featured-size" <?= $selected['stock'] > 0 ? '' : 'disabled' ?>><?php foreach ($sizes as $size): ?><option><?= e($size) ?></option><?php endforeach; ?></select>
            <?php endif; ?>
            <label class="quantity-field" for="featured-qty">Quantity</label>
            <input class="input quantity-input" id="featured-qty" type="number" min="<?= $selected['stock'] > 0 ? 1 : 0 ?>" max="<?= e($selected['stock']) ?>" step="1" value="<?= $selected['stock'] > 0 ? 1 : 0 ?>" <?= $selected['stock'] > 0 ? '' : 'disabled' ?>>
            <div class="featured-buttons">
                <button class="btn btn-primary" id="featured-add-cart" type="button" <?= $selected['stock'] > 0 ? '' : 'disabled' ?>><?= $selected['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?></button>
                <button class="btn btn-secondary" id="view-cart" type="button">View Cart</button>
            </div>
        </div>
    </div>
    <?php else: ?><p class="empty-state">Select a product when the catalog is available.</p><?php endif; ?>
</div>
<dialog class="song-dialog cart-dialog" id="cart-dialog" aria-labelledby="cart-title">
    <button class="dialog-close" data-dismiss-dialog type="button" aria-label="Close cart">×</button>
    <p class="eyebrow">Merchandise</p><h2 id="cart-title">Your cart</h2>
    <p id="cart-adjustment" role="status" hidden></p>
    <div id="cart-items"></div><p id="cart-dialog-total" aria-live="polite"></p>
    <form id="cart-checkout" action="checkout.php" method="post" hidden>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="review">
        <input type="hidden" name="cart" value="">
        <button class="btn btn-primary" type="submit">Proceed to Checkout</button>
    </form>
</dialog>
<script type="application/json" id="product-data"><?= json_encode(cartInventory($catalog), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
</section>
        <section class="section alt">
      <div class="container">

        <div class="section-heading">
          <p class="eyebrow">CHECKOUT GUIDE</p>
          <h2>How to place a demo order.</h2>
        </div>

        <div class="support-grid">

          <article class="support-card">
            <h3>Review your cart</h3>
            <p>Check your items, sizes, and quantities before proceeding to checkout.</p>
          </article>

          <article class="support-card">
            <h3>Enter a delivery address</h3>
            <p>Use sample recipient and address details for this classroom demo.</p>
          </article>

          <article class="support-card">
            <h3>Save your order number</h3>
            <p>The confirmation page shows your reference and the simulated cash-on-delivery total.</p>
          </article>
        </div>
      </div>
    </section>
    <section class="final-cta"><div class="container cta-panel"><p class="eyebrow">Community</p><h2>Want to connect with other fans?</h2><a class="btn btn-primary" href="fan-zone.php">Join Fan Zone</a></div></section>
  </main>
  <div class="cart-toast" id="cart-toast" role="status" aria-live="polite"></div>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <a class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
        <p>A curated fan archive for Queen music, history, live performances, visual media, and community interaction.</p>
      </div>
      <div>
        <h2>Explore</h2>
        <ul class="footer-links">
          <li><a href="music.php">Music</a></li>
          <li><a href="history.php">History &amp; Legacy</a></li>
          <li><a href="gallery.php">Media Gallery</a></li>
          <li><a href="credits.html">Media Credits</a></li>
        </ul>
      </div>
      <div>
        <h2>Official website</h2>
<ul class="footer-links">
<li><a href="https://www.queenonline.com/" target="_blank" rel="noopener">QueenOnline ↗</a></li>
</ul>
      </div>
    </div>
    <p class="copyright">© 2026 Queen Digital Stage. Educational fan archive.</p>
  </footer>
  <script src="assets/js/database-site.js" defer></script><script src="assets/js/cart.js" defer></script></body>
</html>
