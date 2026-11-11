<?php
declare(strict_types=1);

function contentRows(PDO $db, string $sql, array $args = []): array
{
    $statement = $db->prepare($sql);
    $statement->execute($args);
    return $statement->fetchAll();
}

function songs(PDO $db, string $search = '', string $genre = '', string $sort = '', bool $featured = false): array
{
    $conditions = [];
    $params = [];
    if ($featured) {
        $conditions[] = 's.is_featured_song = 1';
    }
    if ($search !== '') {
        $match = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $conditions[] = "(s.song_title LIKE ? ESCAPE '!' OR a.album_title LIKE ? ESCAPE '!' OR s.songwriter_credit LIKE ? ESCAPE '!' OR s.genre LIKE ? ESCAPE '!' OR s.summary LIKE ? ESCAPE '!' OR s.story LIKE ? ESCAPE '!' OR CAST(s.release_year AS CHAR) LIKE ? ESCAPE '!')";
        $params = array_fill(0, 7, $match);
    }
    if ($genre !== '') {
        $conditions[] = 's.genre = ?';
        $params[] = $genre;
    }
    $order = match ($sort) {
        'genre' => 's.genre IS NULL, s.genre, s.song_title',
        'year' => 's.release_year IS NULL, s.release_year, s.song_title',
        default => 's.song_title',
    };
    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    return contentRows($db, 'SELECT s.*, a.album_title, a.cover_url AS album_cover FROM songs s LEFT JOIN albums a ON a.album_id = s.album_id' . $where . ' ORDER BY ' . ($featured ? 's.song_id' : $order . ', s.song_id') . ($featured ? ' LIMIT 3' : ''), $params);
}

function featuredVideo(PDO $db): ?array
{
    return $db->query("SELECT * FROM media_items WHERE media_type = 'video' ORDER BY sort_order, media_id LIMIT 1")->fetch() ?: null;
}

function historyEvents(PDO $db, string $type): array
{
    return contentRows($db, 'SELECT * FROM timeline_events WHERE event_type = ? ORDER BY sort_order, event_id', [$type]);
}

function products(PDO $db, bool $public = true): array
{
    return contentRows($db, 'SELECT p.*, c.category_name FROM products p JOIN product_categories c ON c.category_id = p.category_id' . ($public ? ' WHERE p.is_active = 1' : '') . ' ORDER BY p.product_id');
}

function money(array $product): string
{
    return $product['currency'] . ' ' . number_format((float) $product['price'], 2);
}

function productSizes(array $product): array
{
    return strcasecmp($product['category_name'], 'Apparel') === 0 ? ['S', 'M', 'L', 'XL'] : [];
}

function cartInventory(array $catalog): array
{
    return array_map(static fn(array $product): array => [
        'id' => (int) $product['product_id'],
        'name' => $product['product_name'],
        'stock' => (int) $product['stock'],
        'price' => (string) $product['price'],
        'currency' => $product['currency'],
        'sizes' => productSizes($product),
    ], $catalog);
}

function handleFanSubmission(PDO $db): void
{
    requireCsrf();
    $data = ['name' => postText('name'), 'favorite_topic' => postText('favorite_topic'), 'message' => postText('message')];
    $valid = true;
    foreach (['name' => 100, 'favorite_topic' => 150, 'message' => 2000] as $key => $limit) {
        $value = $data[$key];
        $safe = !str_contains($value, "\0") && mb_check_encoding($value, 'UTF-8');
        $valid = $valid && $safe && $value !== '' && mb_strlen($value, 'UTF-8') <= $limit;
        $data[$key] = $safe ? mb_substr($value, 0, $limit, 'UTF-8') : '';
    }
    if (!$valid) {
        $_SESSION['fan_feedback'] = ['error' => true, 'message' => 'Enter a name (up to 100 characters), a topic (150), and a message (2,000).', 'values' => $data];
    } else {
        $stmt = $db->prepare('INSERT INTO fan_zone_submissions (name, favorite_topic, message) VALUES (?, ?, ?)');
        $stmt->execute(array_values($data));
        $_SESSION['fan_feedback'] = ['error' => false, 'message' => 'Thanks for sharing. Your note is waiting for a moderator.'];
    }
    redirect('fan-zone.php#join-community');
}
