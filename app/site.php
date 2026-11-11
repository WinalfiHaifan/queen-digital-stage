<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/content.php';
require __DIR__ . '/views/components.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Content-Type: text/html; charset=UTF-8');

try {
    $db = database();
    switch ($page) {
        case 'index':
        case 'about':
            $members = contentRows($db, 'SELECT * FROM band_members ORDER BY sort_order, member_id');
            if ($page === 'index') {
                $featuredSongs = songs($db, featured: true);
                $spotlight = featuredVideo($db);
            } else {
                $events = historyEvents($db, 'history');
            }
            break;
        case 'music':
            $albums = contentRows($db, 'SELECT * FROM albums ORDER BY release_year IS NULL, release_year, album_id');
            $genreOptions = $db->query("SELECT DISTINCT genre FROM songs WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);
            $search = mb_substr(queryText('q'), 0, 120, 'UTF-8');
            $genre = queryText('genre');
            $genre = in_array($genre, $genreOptions, true) ? $genre : '';
            $sort = queryText('sort');
            $sort = in_array($sort, ['genre', 'year'], true) ? $sort : '';
            $catalog = songs($db, $search, $genre, $sort);
            $essential = songs($db, featured: true)[0] ?? null;
            break;
        case 'history':
            $events = historyEvents($db, 'history');
            $awards = historyEvents($db, 'award');
            $spotlight = featuredVideo($db);
            break;
        case 'gallery':
            $media = contentRows($db, 'SELECT * FROM media_items ORDER BY sort_order, media_id');
            $photos = array_filter($media, static fn(array $item): bool => $item['media_type'] === 'image');
            $videos = array_filter($media, static fn(array $item): bool => $item['media_type'] === 'video');
            break;
        case 'store':
            $catalog = products($db);
            if (queryText('view') === 'inventory') {
                header('Content-Type: application/json; charset=UTF-8');
                header('Cache-Control: no-store');
                echo json_encode(cartInventory($catalog), JSON_THROW_ON_ERROR);
                exit;
            }
            startSession();
            header('Cache-Control: no-store');
            $categories = contentRows($db, 'SELECT * FROM product_categories ORDER BY category_id');
            $selected = $catalog[0] ?? null;
            foreach ($catalog as $item) {
                if ((string) $item['product_id'] === queryText('product')) {
                    $selected = $item;
                    break;
                }
            }
            break;
        case 'fan-zone':
            startSession();
            header('Cache-Control: no-store');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                handleFanSubmission($db);
            }
            $feedback = $_SESSION['fan_feedback'] ?? [];
            unset($_SESSION['fan_feedback']);
            $notes = contentRows($db, 'SELECT name, favorite_topic, message, created_at FROM fan_zone_submissions WHERE is_active = 1 ORDER BY created_at DESC, submission_id DESC LIMIT 3');
            break;
    }
} catch (PDOException $error) {
    unavailable($error);
}

require __DIR__ . '/views/' . $page . '.php';
