<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<link href="assets/images/favicon.svg" rel="icon" type="image/svg+xml">
<title>Media Gallery | Queen Digital Stage</title>
<meta content="Queen media gallery with photos, album covers, archive images, and YouTube references." name="description">
<link href="assets/css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-gallery" data-page="gallery">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
<div class="container nav-wrap">
<a aria-label="Queen Digital Stage Home" class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
<button aria-expanded="false" aria-label="Open navigation" class="nav-toggle" type="button">☰</button>
<nav aria-label="Main navigation" class="site-nav">
<a class="" href="index.php">Home</a>
<a class="" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a class="" href="history.php">History</a>
<a aria-current="page" class="active" href="gallery.php">Media</a>
<a class="" href="fan-zone.php">Fan Zone</a>
<a class="" href="store.php">Store</a>
</nav>
</div>
</header>
<main id="main-content">
<section class="hero gallery-hero">
<div class="container hero-grid">
<div class="hero-copy">
<p class="eyebrow">Media Gallery</p>
<h1>
The Visual Legacy Of Queen.
</h1>

<p class="lead">
Explore rare photographs, legendary performances, iconic album artwork, and unforgettable moments from Queen's musical journey.
</p>
<div class="button-row">
<a class="btn btn-primary" href="#gallery">View Gallery</a>
<a class="btn btn-secondary" href="#videos">Video Archive</a>
</div>
</div>
<figure class="hero-card">
<img alt="Queen visual archive cover" decoding="async" src="assets/images/hero-queen-logo.webp">
<figcaption>Featured visual: Queen archive identity.</figcaption>
</figure>
</div>
</section>
<section class="section" id="gallery">
<div class="container">
<div class="section-grid">
<div>
<p class="eyebrow">Curated Collection</p>
<h2>Photos, live moments, album era, and archive material.</h2>
<p>Choose a category to explore the archive. Select a photo to see it in more detail.</p>
</div>
</div>
<div aria-label="Gallery filters" class="gallery-toolbar" role="group">
<button class="filter-btn active" data-filter="all" type="button">All</button>
<button class="filter-btn" data-filter="band" type="button">Band Photos</button>
<button class="filter-btn" data-filter="member" type="button">Members</button>
<button class="filter-btn" data-filter="live" type="button">Live Moments</button>
<button class="filter-btn" data-filter="album" type="button">Album Covers</button>
<button class="filter-btn" data-filter="archive" type="button">Archive</button>
</div>
<div class="gallery-grid">
<?php foreach ($photos as $photo): ?>
    <figure class="gallery-card" data-category="<?= e($photo['category']) ?>">
        <?php if ($url = assetUrl($photo['media_url'])): ?>
        <button class="gallery-open" type="button" data-gallery-image="<?= e($url) ?>" data-gallery-title="<?= e($photo['media_title']) ?>" aria-label="Enlarge <?= e($photo['media_title']) ?>">
            <?php coverImage($url, $photo['media_title']); ?>
        </button>
        <?php endif; ?>
        <figcaption><strong><?= e($photo['media_title']) ?></strong><span><?= e(implode(' · ', array_filter([ucfirst($photo['category']), $photo['media_year']]))) ?></span>
            <?php if ($photo['description']): ?><span><?= e($photo['description']) ?></span><?php endif; ?>
            <?php sourceLink($photo['source_url']); ?>
        </figcaption>
    </figure>
<?php endforeach; ?>
</div>
<?php if ($photos): ?>
<p class="empty-state" id="gallery-empty" role="status" hidden>No photos in this category.</p>
<?php else: ?>
<p class="empty-state">Archive photos will appear here.</p>
<?php endif; ?>
</div>
</section>
<section class="section alt" id="videos">
<div class="container">
<div class="section-heading">
<p class="eyebrow">Video Archive</p>
<h2>Selected Queen videos.</h2>
<p>Explore performances and official videos from the archive.</p>
</div>
<div class="video-grid">
<?php foreach ($videos as $video): ?>
    <article class="video-card">
        <?php videoLink($video); ?>
        <div class="video-body"><h3><?= e($video['media_title']) ?></h3><p><?= e($video['description']) ?></p><?php sourceLink($video['source_url']); ?></div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$videos): ?><p class="empty-state">Archive videos will appear here.</p><?php endif; ?>
</div>
</section>
<section class="final-cta">
<div class="container cta-panel">
<p class="eyebrow">Behind The Moments</p>
<h2>Want to know the story behind these moments?</h2>
<a class="btn btn-primary" href="history.php">Explore History</a>
</div>
</section>
</main>
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
<!-- Image Lightbox -->
<script src="assets/js/database-site.js" defer></script><dialog class="song-dialog gallery-dialog" id="gallery-dialog" aria-labelledby="gallery-title"><button class="dialog-close" data-dismiss-dialog type="button" aria-label="Close image">×</button><h2 id="gallery-title">Archive image</h2><img alt=""></dialog></body>
</html>
