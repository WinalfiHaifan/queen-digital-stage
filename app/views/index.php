<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<link href="assets/images/favicon.svg" rel="icon" type="image/svg+xml">
<title>Home | Queen Digital Stage</title>
<meta content="Queen Digital Stage home page featuring Queen music, history, live performance, and visual archive content." name="description">
<link href="assets/css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-home" data-page="index">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
<div class="container nav-wrap">
<a aria-label="Queen Digital Stage Home" class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
<button aria-expanded="false" aria-label="Open navigation" class="nav-toggle" type="button">☰</button>
<nav aria-label="Main navigation" class="site-nav">
<a aria-current="page" class="active" href="index.php">Home</a>
<a class="" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a class="" href="history.php">History</a>
<a class="" href="gallery.php">Media</a>
<a class="" href="fan-zone.php">Fan Zone</a>
<a class="" href="store.php">Store</a>
</nav>
</div>
</header>
<main id="main-content">
<section class="hero hero-home">
<div class="container hero-layout">
<div class="hero-copy">
<p class="eyebrow">THE LEGACY OF QUEEN</p>
<h1>Queen Digital Stage</h1>
<p class="lead">
Explore Queen through legendary songs, unforgettable performances, timeless visuals, and the legacy of one of rock's greatest bands.
</p><div class="cta-row">
<a class="btn btn-primary" href="music.php">Explore Music</a>
<a class="btn btn-secondary" href="history.php">Read History</a>
</div>
<div class="kicker-line"></div>
</div>
<div aria-label="Queen hero artwork" class="hero-panel" role="img">
<img alt="Queen logo artwork in gold and dark color palette" decoding="async" src="assets/images/hero-queen-logo.webp">
<div class="hero-panel-caption"><strong>1970</strong><span>London • Rock • Legacy</span></div>
</div>
</div>
</section>
<section class="section tight">
<div class="container card-grid three">
<a class="card explore-card" href="music.php">
<span>Music</span>
<h3>Essential Queen songs</h3>
<p>Explore iconic tracks from Queen’s most recognizable eras.</p>
</a>
<a class="card explore-card" href="history.php">
<span>History</span>

<h3>Discover Queen's journey</h3>

<p>Explore the band's formation, milestones, and lasting legacy.</p>
</a>
<a class="card explore-card" href="gallery.php">
<span>Media</span>

<h3>Explore visual moments</h3>

<p>Discover Queen's iconic performances, photos, and visual archive.</p>
</a>
</div>
</section>
<section class="section alt">
<div class="container">
<div class="section-heading split">
<div>
<p class="eyebrow">Featured Music</p>
<h2>Iconic songs first.</h2>
</div>
<a class="text-link" href="music.php">Open Music →</a>
</div>
<div class="song-grid">
<?php foreach ($featuredSongs as $song) songCard($song, true); ?>
<?php if (!$featuredSongs): ?><p class="empty-state">Featured songs will appear here.</p><?php endif; ?>
</div>
</div>
</section>
<section class="section">
<div class="container two-col wide-left">
<?php if ($spotlight): ?>
<article class="video-card"><?php videoLink($spotlight, 'video-shell'); ?></article>
<div>
    <p class="eyebrow">Featured Performance</p>
    <h2><?= e($spotlight['media_title']) ?></h2>
    <p><?= e($spotlight['description']) ?></p>
    <a class="btn btn-secondary" href="gallery.php#videos">Explore the Video Archive</a>
</div>
<?php else: ?><p class="empty-state">A performance will appear here when the archive has videos.</p><?php endif; ?>
</div>
</section>
<section class="section alt">
<div class="container two-col">
<div>
<p class="eyebrow">About Queen</p>
<h2>Four classic members. One legendary sound.</h2>
<ul class="lineup-summary">
    <?php foreach ($members as $member): ?>
        <li><strong><?= e($member['member_name']) ?></strong><span><?= e($member['role']) ?></span></li>
    <?php endforeach; ?>
</ul>
<?php if (!$members): ?><p class="empty-state">Band profiles will appear here.</p><?php endif; ?>
<a class="btn btn-secondary" href="about.php">Meet the Members</a>
</div>
<img alt="Classic black and white photo of Queen as a group" class="rounded-media" decoding="async" loading="lazy" src="assets/images/queen-group-classic.webp">
</div>
</section>
<section class="final-cta">
<div class="container cta-panel">
<p class="eyebrow">Fan Community</p>
<h2>
Connect with Queen fans worldwide.
</h2>
<p>Share favorite songs, discuss live moments, and connect with other fans.</p>
<a class="btn btn-primary" href="fan-zone.php">Join Fan Zone</a>
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
<script src="assets/js/database-site.js" defer></script></body>
</html>
