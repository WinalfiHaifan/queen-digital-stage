<?php
declare(strict_types=1);

function coverImage(?string $url, string $title, string $class = ''): void
{
    $path = assetUrl($url);
    if ($path !== ''): ?>
        <img class="<?= e($class) ?>" src="<?= e($path) ?>" alt="<?= e($title) ?>" loading="lazy" decoding="async">
    <?php else: ?>
        <span class="cover-placeholder <?= e($class) ?>" aria-hidden="true">Q</span>
    <?php endif;
}

function videoLink(array $video, string $class = 'video-preview'): void
{
    $url = webUrl($video['media_url']);
    if ($url === '') {
        return;
    }
    $id = mediaYoutubeId($url); ?>
    <a class="<?= e($class) ?> dynamic-video" href="<?= e($url) ?>" data-youtube-id="<?= e($id) ?>" target="_blank" rel="noopener" aria-label="Play <?= e($video['media_title']) ?>">
        <?php coverImage($video['thumbnail_url'], '', 'video-thumbnail'); ?>
        <span class="play-button" aria-hidden="true">▶</span>
        <span class="video-label"><?= e($video['media_title']) ?></span>
    </a>
<?php }

function sourceLink(?string $url, string $label = 'Source'): void
{
    if ($path = webUrl($url)): ?>
        <a class="text-link content-source" href="<?= e($path) ?>" target="_blank" rel="noopener"><?= e($label) ?> ↗</a>
    <?php endif;
}

function songCard(array $song, bool $withCover = false): void
{
    $spotify = spotifyEmbed($song['spotify_url'], 'track');
    $videoId = youtubeId($song['youtube_video_id']);
    $id = 'song-detail-' . (int) $song['song_id'];
    $summary = $song['summary'] ?? mb_strimwidth($song['story'] ?? '', 0, 160, '…', 'UTF-8'); ?>
    <article class="song-card dynamic-song">
        <?php if ($withCover) coverImage($song['cover_url'] ?: $song['album_cover'], $song['song_title']); ?>
        <div class="song-card-body">
            <div class="song-meta">
                <?php if ($song['release_year']): ?><span class="song-year"><?= e($song['release_year']) ?></span><?php endif; ?>
                <?php if ($song['genre']): ?><span class="data-chip"><?= e($song['genre']) ?></span><?php endif; ?>
            </div>
            <h3><?= e($song['song_title']) ?></h3>
            <?php if ($song['album_title']): ?><p class="song-album"><?= e($song['album_title']) ?></p><?php endif; ?>
            <?php if ($song['songwriter_credit']): ?><p class="song-writer">Written by <?= e($song['songwriter_credit']) ?></p><?php endif; ?>
            <?php if ($summary !== ''): ?><p class="song-preview"><?= e($summary) ?></p><?php endif; ?>
            <div class="content-actions">
                <button class="story-btn" type="button" data-song-detail="<?= e($id) ?>">Read story</button>
                <?php if ($videoId !== ''): ?><a class="text-link" href="https://www.youtube.com/watch?v=<?= e($videoId) ?>" data-youtube-id="<?= e($videoId) ?>" target="_blank" rel="noopener">Watch video</a><?php endif; ?>
            </div>
        </div>
        <dialog class="song-dialog" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-title">
            <button class="dialog-close" data-dismiss-dialog type="button" aria-label="Close song details">×</button>
            <div class="dialog-kicker"><?= e(implode(' · ', array_filter([$song['release_year'], $song['genre'], $song['album_title']]))) ?></div>
            <h2 id="<?= e($id) ?>-title"><?= e($song['song_title']) ?></h2>
            <?php if ($song['songwriter_credit']): ?><p class="song-writer">Written by <?= e($song['songwriter_credit']) ?></p><?php endif; ?>
            <p class="dialog-story"><?= e($song['story'] ?: ($summary ?: 'No story is available yet.')) ?></p>
            <?php if ($spotify): ?><iframe class="song-player" title="Listen to <?= e($song['song_title']) ?> on Spotify" data-player-src="<?= e($spotify) ?>" allow="autoplay; encrypted-media; picture-in-picture" loading="lazy"></iframe><?php endif; ?>
            <?php if ($videoId): ?><a class="text-link" href="https://www.youtube.com/watch?v=<?= e($videoId) ?>" target="_blank" rel="noopener">Watch on YouTube ↗</a><?php endif; ?>
        </dialog>
    </article>
<?php }
