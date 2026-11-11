<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<link href="assets/images/favicon.svg" rel="icon" type="image/svg+xml">
<title>History | Queen Digital Stage</title>
<meta content="History and legacy page with timeline and Live Aid video." name="description">
<link href="assets/css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-history" data-page="history">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
<div class="container nav-wrap">
<a aria-label="Queen Digital Stage Home" class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
<button aria-expanded="false" aria-label="Open navigation" class="nav-toggle" type="button">☰</button>
<nav aria-label="Main navigation" class="site-nav">
<a class="" href="index.php">Home</a>
<a class="" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a aria-current="page" class="active" href="history.php">History</a>
<a class="" href="gallery.php">Media</a>
<a class="" href="fan-zone.php">Fan Zone</a>
<a class="" href="store.php">Store</a>
</nav>
</div>
</header>
<main class="history-page" id="main-content">
<section class="page-hero history-hero">
<div class="container"><p class="eyebrow">History &amp; Legacy</p><h1>From London to legend.</h1><p class="lead">Follow Queen’s journey from their formation in 1970 to the performances, songs, and cultural influence that defined their legacy.</p></div>
</section>
<section class="section history-timeline">

<div class="container">

<div class="section-heading">

<p class="eyebrow">
Timeline
</p>

<h2>
Queen Through The Years
</h2>

<p>
Follow Queen's evolution from a London rock band into a global musical legend.
</p>

</div>


<div class="timeline-wrapper">
<div class="timeline-line"></div>
<?php foreach ($events as $event): ?>
    <article class="timeline-item show" id="event-<?= e($event['event_id']) ?>">
        <div class="timeline-dot"></div>
        <div class="timeline-card">
            <span class="timeline-year"><?= e($event['event_year'] ?: 'Legacy') ?></span>
            <h3><?= e($event['event_title']) ?></h3>
            <p><?= e($event['description']) ?></p>
            <?php sourceLink($event['source_url'], 'Read the source'); ?>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$events): ?><p class="empty-state">Timeline entries will appear here.</p><?php endif; ?>

</div>

</section>
<section class="section alt">
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
<div class="container">
<div class="awards-header">
<div class="section-heading">
<p class="eyebrow">Awards &amp; Achievements</p>
<h2>Recognized across generations.</h2>
</div>
<?php if ($awards): ?>
<div class="slider-nav">
<button aria-label="Previous awards" class="nav-btn award-scroll-btn" data-awards-direction="-1" type="button">←</button>
<button aria-label="Next awards" class="nav-btn award-scroll-btn" data-awards-direction="1" type="button">→</button>
</div>
<?php endif; ?>
</div>
<div class="awards-wrapper" id="awardsWrapper">
<?php foreach ($awards as $award): ?>
    <article class="award-card">
        <?php coverImage($award['image_url'], $award['event_title']); ?>
        <h3><?= e($award['event_title']) ?></h3>
        <?php if ($award['event_year']): ?><span class="data-chip"><?= e($award['event_year']) ?></span><?php endif; ?>
        <p><?= e($award['description']) ?></p>
        <?php sourceLink($award['source_url']); ?>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$awards): ?><p class="empty-state">Awards will appear here.</p><?php endif; ?>
</div>
</section>
<section class="final-cta"><div class="container cta-panel"><p class="eyebrow">Next Step</p><h2>Hear the songs behind the story.</h2><a class="btn btn-primary" href="music.php">Explore Music</a></div></section>
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
