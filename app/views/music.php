<?php
$videoId = youtubeId($essential['youtube_video_id'] ?? null);
$spotify = spotifyEmbed($essential['spotify_url'] ?? null, 'track');
$hasFeaturedPlayer = $videoId || $spotify;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<link href="assets/images/favicon.svg" rel="icon" type="image/svg+xml">
<title>Music | Queen Digital Stage</title>
<meta content="Explore Queen albums, search songs by genre, and play selected tracks through YouTube and Spotify." name="description">
<link href="assets/css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/database-site.css"></head>
<body class="page-music" data-page="music">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
<div class="container nav-wrap">
<a aria-label="Queen Digital Stage Home" class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a>
<button aria-expanded="false" aria-label="Open navigation" class="nav-toggle" type="button"><span aria-hidden="true">☰</span></button>
<nav aria-label="Main navigation" class="site-nav">
<a href="index.php">Home</a>
<a href="about.php">About</a>
<a aria-current="page" class="active" href="music.php">Music</a>
<a href="history.php">History</a>
<a href="gallery.php">Media</a>
<a href="fan-zone.php">Fan Zone</a>
<a href="store.php">Store</a>
</nav>
</div>
</header>
<main id="main-content">
<section class="hero-section music-hero-enhanced">
<div class="container hero-layout">
<div class="hero-text">
<p class="eyebrow">Music Experience</p>
<h1>Listen. Search. Discover.</h1>
<p class="lead">Explore a searchable Queen song library, play official media without leaving the website, and open a concise story for every song listed here.</p>
<div class="hero-actions">
<?php if ($hasFeaturedPlayer): ?>
<a class="btn btn-primary" href="#essential-song">Open Media Player</a>
<?php endif; ?>
<a class="btn <?= $hasFeaturedPlayer ? 'btn-secondary' : 'btn-primary' ?>" href="#song-explorer">Browse Songs</a>
</div>
<ul aria-label="Music page features" class="feature-pills">
<li>Embedded YouTube</li><li>Embedded Spotify</li><li>Song Stories</li>
</ul>
</div>
<figure class="hero-card floating-card">
<img alt="Queen group portrait with a dramatic dark background" decoding="async" fetchpriority="high" src="assets/images/queen-group-shadow-music.webp">
<figcaption><strong>Queen Music Hub</strong><span>One page for songs, stories, video, and streaming.</span></figcaption>
</figure>
</div>
</section>
<?php if ($hasFeaturedPlayer): ?>
<section class="section essential-section" id="essential-song">
<div class="container">
    <div class="section-heading">
        <p class="eyebrow">Essential Song</p><h2><?= e($essential['song_title']) ?></h2>
        <p><?= e($essential['summary']) ?></p>
    </div>
    <div class="essential-grid">
        <article class="essential-video card card-pad">
            <div class="embed-card-header"><?php if ($videoId): ?><span class="platform-badge youtube-badge">YouTube</span><?php endif; ?><h3><?= e($essential['song_title']) ?></h3></div>
            <?php if ($videoId): ?>
                <?php videoLink(['media_url' => 'https://www.youtube.com/watch?v=' . $videoId, 'media_title' => $essential['song_title'], 'thumbnail_url' => $essential['cover_url'] ?: $essential['album_cover']]); ?>
            <?php else: ?>
                <?php coverImage($essential['cover_url'] ?: $essential['album_cover'], $essential['song_title']); ?>
            <?php endif; ?>
        </article>
        <article class="essential-spotify card card-pad">
            <div class="embed-card-header"><?php if ($spotify): ?><span class="platform-badge spotify-badge">Spotify</span><?php endif; ?><h3><?= e($essential['album_title'] ?: $essential['song_title']) ?></h3></div>
            <?php if ($spotify): ?><iframe class="song-player" src="<?= e($spotify) ?>" title="Listen to <?= e($essential['song_title']) ?> on Spotify" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture"></iframe><?php endif; ?>
            <p><?= e(implode(' · ', array_filter([$essential['release_year'], $essential['genre'], $essential['songwriter_credit']]))) ?></p>
            <a class="text-link" href="#song-explorer">Explore songs</a>
        </article>
    </div>
</div>
</section>
<?php endif; ?>

<!-- ALBUM COLLECTION -->

<section class="section album-section">

<div class="container">

<div class="section-heading">
<p class="eyebrow">Albums</p>

<h2>
Explore Queen Albums
</h2>

<p>
Discover the albums that shaped Queen's musical journey.
</p>

</div>


<div class="album-carousel-wrapper">


<button class="album-arrow left" id="album-prev" type="button" aria-label="Previous albums">
←
</button>



<div class="album-carousel" id="album-carousel">
<?php foreach ($albums as $album): ?>
    <article class="album-card">
        <?php coverImage($album['cover_url'], $album['album_title'], 'album-cover'); ?>
        <div class="album-metadata">
            <?php if ($album['release_year']): ?><span class="album-year"><?= e($album['release_year']) ?></span><?php endif; ?>
            <?php if ($album['genre']): ?><span class="data-chip"><?= e($album['genre']) ?></span><?php endif; ?>
        </div>
        <h3><?= e($album['album_title']) ?></h3>
        <p><?= e($album['description']) ?></p>
        <?php if ($spotify = spotifyEmbed($album['spotify_url'], 'album')):
            $albumDialog = 'album-player-' . (int) $album['album_id']; ?>
            <button class="btn btn-secondary" type="button" data-song-detail="<?= e($albumDialog) ?>">Listen on Spotify</button>
            <dialog class="song-dialog" id="<?= e($albumDialog) ?>" aria-labelledby="<?= e($albumDialog) ?>-title">
                <button class="dialog-close" data-dismiss-dialog type="button" aria-label="Close album player">×</button>
                <p class="eyebrow">Album</p><h2 id="<?= e($albumDialog) ?>-title"><?= e($album['album_title']) ?></h2>
                <iframe class="song-player" title="Listen to <?= e($album['album_title']) ?> on Spotify" data-player-src="<?= e($spotify) ?>" allow="autoplay; encrypted-media; picture-in-picture"></iframe>
                <a class="text-link" href="<?= e(str_replace('/embed/', '/', explode('?', $spotify)[0])) ?>" target="_blank" rel="noopener">Open Spotify ↗</a>
            </dialog>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
<?php if (!$albums): ?><p class="empty-state">No albums are available yet.</p><?php endif; ?>
</div>



<button class="album-arrow right" id="album-next" type="button" aria-label="Next albums">
→
</button>


</div>

</div>

</section>

<!-- SONG EXPLORER -->

<section class="section explore-section" id="song-explorer">

<div class="container">


<div class="section-heading">

<p class="eyebrow">
Explore Songs
</p>


<h2>
Every Song Has A Story
</h2>


<p>
Search Queen songs and uncover the story behind every masterpiece.
</p>


</div>



<form class="song-filters" action="music.php#song-explorer" method="get" role="search">
    <label>Search songs<input class="input" id="song-search" name="q" type="search" maxlength="120" placeholder="Title, album, writer or year" value="<?= e($search) ?>"></label>
    <label>Genre<select class="input" name="genre"><option value="">All genres</option><?php foreach ($genreOptions as $option): ?><option value="<?= e($option) ?>" <?= $option === $genre ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></label>
    <label>Sort by<select class="input" name="sort"><option value="">Title</option><option value="genre" <?= $sort === 'genre' ? 'selected' : '' ?>>Genre</option><option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Release year</option></select></label>
    <button class="btn btn-primary" type="submit">Apply</button>
    <?php if ($search || $genre || $sort): ?><a class="text-link" href="music.php#song-explorer">Clear</a><?php endif; ?>
</form>
<p class="song-helper" role="status"><?= count($catalog) ?> <?= count($catalog) === 1 ? 'song' : 'songs' ?></p>
<div class="song-explorer-grid" id="song-explorer-grid">
<?php foreach ($catalog as $song) songCard($song); ?>
<?php if (!$catalog): ?><p class="empty-state">No songs match these filters.</p><?php endif; ?>
</div>


</div>


</section>

<section class="final-cta">
<div class="container cta-panel">
<p class="eyebrow">Continue Exploring</p>
<h2>See the performances behind the music.</h2>
<p>Open the media archive for band photography, album artwork, and embedded video highlights.</p>
<a class="btn btn-primary" href="gallery.php#videos">View Media Archive</a>
</div>
</section>
</main>
<footer class="site-footer">
<div class="container footer-grid">
<div><a class="brand" href="index.php"><span class="brand-mark">Q</span><span>Queen Digital Stage</span></a><p>A curated fan archive for Queen music, history, live performances, visual media, and community interaction.</p></div>
<div><h2>Explore</h2><ul class="footer-links"><li><a href="music.php">Music</a></li><li><a href="history.php">History &amp; Legacy</a></li><li><a href="gallery.php">Media Gallery</a></li><li><a href="credits.html">Media Credits</a></li></ul></div>
<div><h2>Official website</h2>
<ul class="footer-links">
<li><a href="https://www.queenonline.com/" target="_blank" rel="noopener">QueenOnline ↗</a></li>
</ul></div>
</div>
<p class="copyright">© 2026 Queen Digital Stage. Educational fan archive.</p>
</footer>
<script src="assets/js/database-site.js" defer></script></body>
</html>
