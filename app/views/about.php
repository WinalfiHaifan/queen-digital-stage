<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<link href="assets/images/favicon.svg" rel="icon" type="image/svg+xml">
<title>About | Queen Digital Stage</title>
<meta content="About Queen page with four classic members and concise band overview." name="description">
<link href="assets/css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-about" data-page="about">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
<div class="container nav-wrap">
<a aria-label="Queen Digital Stage Home" class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
<button aria-expanded="false" aria-label="Open navigation" class="nav-toggle" type="button">☰</button>
<nav aria-label="Main navigation" class="site-nav">
<a class="" href="index.php">Home</a>
<a aria-current="page" class="active" href="about.php">About</a>
<a class="" href="music.php">Music</a>
<a class="" href="history.php">History</a>
<a class="" href="gallery.php">Media</a>
<a class="" href="fan-zone.php">Fan Zone</a>
<a class="" href="store.php">Store</a>
</nav>
</div>
</header>
<main id="main-content">
<section class="hero">
<div class="container hero-content">
<p class="eyebrow">About Queen</p>
<h1>The band behind the stage.</h1>
<p class="lead">
          A focused profile of Queen, their classic lineup, and the legacy
          behind their music and performances.
        </p>
</div>
</section>
<section class="section">
<div class="container about-intro">
<div class="intro-text">
<p class="eyebrow">Overview</p>
<h2>Rock, theatre, harmony, and scale.</h2>
<p>
            Queen was formed in London in 1970 and became known for bold vocal
            arrangements, theatrical stage energy, and genre-blending rock songs.
            This page introduces Queen’s identity, classic members, key facts,
            and legacy before visitors explore the music and history in detail.
          </p>
</div>
<div class="intro-image">
<img alt="Queen group portrait" decoding="async" src="assets/images/queen-group-shadow.webp">
</div>
</div>
</section>
<section class="section section-dark">
<div class="container">
<div class="section-heading">
<p class="eyebrow">Key Facts</p>
<h2>Quick facts.</h2>
</div>
<div class="facts-list">
<article class="fact-card">
<strong>1970</strong>
<p>Formed in London</p>
</article>
<article class="fact-card">
<strong>Rock</strong>
<p>Genre foundation</p>
</article>
<article class="fact-card">
<strong>4</strong>
<p>Classic lineup members</p>
</article>
<article class="fact-card">
<strong>Live</strong>
<p>Known for stage performance</p>
</article>
</div>
</div>
</section>
<section class="section">
<div class="container">
<div class="section-heading heading-row">
<div>
<p class="eyebrow">Members</p>
<h2>Classic lineup.</h2>
</div>
<a class="text-link" href="history.php">Read History →</a>
</div>
<div class="members-list">
<?php foreach ($members as $member): ?>
    <article class="member-card">
        <?php coverImage($member['photo_url'], $member['member_name']); ?>
        <div class="member-body">
            <span class="role"><?= e($member['role']) ?></span>
            <h3><?= e($member['member_name']) ?></h3>
            <?php if ($member['birth_date']): ?>
            <p class="member-birth">Born <time datetime="<?= e($member['birth_date']) ?>"><?= e((new DateTimeImmutable($member['birth_date']))->format('j F Y')) ?></time></p>
            <?php endif; ?>
            <p><?= e($member['biography']) ?></p>
        </div>
    </article>
<?php endforeach; ?>
<?php if (!$members): ?><p class="empty-state">No profiles are available yet.</p><?php endif; ?>
</div>
</div>
</section>
<section class="section section-dark">

<div class="container">

<div class="section-heading">

<p class="eyebrow">
Short Timeline
</p>

<h2>
From London To The World
</h2>

<p>
Explore defining moments from Queen's story.
Continue to the History page for the complete timeline.
</p>

</div>


<div class="short-timeline">
<?php $milestones = count($events) > 4 ? [$events[0], $events[(int) floor(count($events) / 3)], $events[(int) floor(count($events) * 2 / 3)], $events[count($events) - 1]] : $events; ?>
<div class="timeline-line"></div>
<?php foreach ($milestones as $event): ?>
<div class="timeline-item">
    <span class="timeline-dot"></span>
    <time><?= e($event['event_year'] ?: 'Legacy') ?></time>
    <h3><a href="history.php#event-<?= e($event['event_id']) ?>"><?= e($event['event_title']) ?></a></h3>
    <p><?= e(mb_strimwidth($event['description'], 0, 95, '…', 'UTF-8')) ?></p>
</div>
<?php endforeach; ?>
</div>
<?php if (!$milestones): ?><p class="empty-state">Timeline milestones will appear here.</p><?php endif; ?>

</div>
</section>

<section class="cta-section">
<div class="container cta-box">
<p class="eyebrow">Next Step</p>
<h2>Ready to hear the songs behind the story?</h2>
<div class="cta-actions">
<a class="btn btn-primary" href="music.php">Explore Music</a>
<a class="btn btn-secondary" href="history.php">Read History</a>
</div>
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
