<?php
declare(strict_types=1);

function recordId(string $raw): int
{
    return ctype_digit($raw) && (float) $raw <= 4294967295 ? (int) $raw : 0;
}

function findRecord(PDO $db, string $table, string $pk, int $id): ?array
{
    $stmt = $db->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function adminUrl(string $tab, array $extra = []): string
{
    return '?' . http_build_query(['tab' => $tab] + $extra);
}

function notice(string $message): void
{
    $_SESSION['admin_notice'] = $message;
}

function adminLoginAllowed(bool $reset = false): ?bool
{
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'queen-login-' . hash('sha256', realpath(PROJECT_ROOT)) . '.json';
    $file = @fopen($path, 'c+');
    if (!$file) {
        return null;
    }
    try {
        if (!flock($file, LOCK_EX)) {
            return null;
        }
        $contents = stream_get_contents($file);
        if ($contents === false) {
            return null;
        }
        $buckets = json_decode($contents, true) ?? [];
        $now = time();
        foreach ($buckets as $key => $times) {
            $recent = array_values(array_filter($times, static fn(int $when): bool => $when > $now - 300));
            if ($recent) {
                $buckets[$key] = $recent;
            } else {
                unset($buckets[$key]);
            }
        }
        $client = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
        if ($reset) {
            unset($buckets[$client]);
        } else {
            if (count($buckets[$client] ?? []) >= 8) {
                return false;
            }
            $buckets[$client][] = $now;
        }
        $payload = json_encode($buckets, JSON_THROW_ON_ERROR);
        if (!rewind($file) || !ftruncate($file, 0) || fwrite($file, $payload) !== strlen($payload) || !fflush($file)) {
            return null;
        }
        return true;
    } finally {
        fclose($file);
    }
}

function signIn(PDO $db): string
{
    $allowed = adminLoginAllowed();
    if ($allowed === null) {
        http_response_code(503);
        return 'Sign-in is temporarily unavailable. Please try again.';
    }
    if (!$allowed) {
        http_response_code(429);
        return 'Too many attempts. Wait five minutes and try again.';
    }
    $login = postText('login');
    $password = $_POST['password'] ?? '';
    if (!is_string($password) || strlen($password) > 72 || str_contains($password, "\0") || $login === '' || strlen($login) > 254) {
        return 'Invalid sign-in details.';
    }
    // Match the username first when a login string also happens to be an email.
    $statement = $db->prepare('SELECT admin_id, password_hash, is_active FROM admin_users WHERE username = ? OR email = ? ORDER BY (username = ?) DESC LIMIT 1');
    $statement->execute([$login, $login, $login]);
    $record = $statement->fetch();
    if (!$record || !password_verify($password, $record['password_hash']) || !$record['is_active']) {
        return 'Invalid sign-in details.';
    }
    adminLoginAllowed(reset: true);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $record['admin_id'];
    $_SESSION['admin_seen'] = time();
    redirect(adminUrl('dashboard'));
}

function sessionAdmin(PDO $db): ?array
{
    if (empty($_SESSION['admin_id']) || ($_SESSION['admin_seen'] ?? 0) < time() - 3600) {
        unset($_SESSION['admin_id'], $_SESSION['admin_seen']);
        return null;
    }
    $statement = $db->prepare('SELECT admin_id, full_name FROM admin_users WHERE admin_id = ? AND is_active = 1');
    $statement->execute([$_SESSION['admin_id']]);
    $admin = $statement->fetch();
    if (!$admin) {
        unset($_SESSION['admin_id'], $_SESSION['admin_seen']);
        return null;
    }
    $_SESSION['admin_seen'] = time();
    return $admin;
}

function validDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value && (int) substr($value, 0, 4) >= 1000;
}

function validValue(string $type, mixed $limit, string $value): bool
{
    if (str_contains($value, "\0") || !mb_check_encoding($value, 'UTF-8')) {
        return false;
    }
    if (is_int($limit) && $limit > 0 && mb_strlen($value, 'UTF-8') > $limit) {
        return false;
    }
    return match ($type) {
        'flag' => in_array($value, ['0', '1'], true),
        'uint' => ctype_digit($value) && (float) $value <= 4294967295,
        'year' => preg_match('/\A[0-9]{4}\z/', $value) && (int) $value >= 1901 && (int) $value <= 2155,
        'decimal' => (bool) preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $value),
        'currency' => (bool) preg_match('/\A[A-Z]{3}\z/', $value),
        'date' => validDate($value),
        'url' => webUrl($value) !== '',
        'asset' => assetUrl($value) !== '',
        'media' => postText('media_type') === 'image' ? assetUrl($value) !== '' : webUrl($value) !== '',
        'youtube' => youtubeId($value) !== '',
        'spotify-album' => spotifyEmbed($value, 'album') !== '',
        'spotify-track' => spotifyEmbed($value, 'track') !== '',
        'album', 'category' => recordId($value) > 0,
        'choice' => in_array($value, $limit, true),
        'email' => (bool) filter_var($value, FILTER_VALIDATE_EMAIL),
        'username' => (bool) preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $value),
        'password' => strlen($value) >= 12 && strlen($value) <= 72 && !str_contains($value, "\0"),
        default => true,
    };
}

function saveRecord(PDO $db, string $table, string $pk, array $fields, int $id, int $adminId, array &$values): array
{
    $errors = [];
    $data = [];
    $existing = $id ? findRecord($db, $table, $pk, $id) : null;
    if ($id && !$existing) {
        return ['The record no longer exists.'];
    }
    $checkStock = $id && $table === 'products';
    $expectedStock = $checkStock ? postText('stock_at_open') : '';
    if ($checkStock) {
        $values['stock_at_open'] = $expectedStock;
        if ($expectedStock !== (string) $existing['stock']) {
            $errors[] = 'Stock changed while this form was open. Reopen the product to see the current inventory.';
        }
    }
    foreach ($fields as $key => $field) {
        [$label, $type] = $field;
        $limit = $field[2] ?? 0;
        $nullable = array_key_exists(3, $field) && $field[3] === null;
        $password = $_POST['password'] ?? '';
        $value = $type === 'password' ? (is_string($password) ? $password : '') : postText($key);
        if ($type === 'flag' && !isset($_POST[$key])) {
            $value = '0';
        }
        if ($type !== 'password') {
            $values[$key] = $value;
        }
        if ($type === 'password' && $value === '' && $id) {
            continue;
        }
        if ($value === '' && $nullable && $type !== 'password') {
            $data[$key] = null;
            continue;
        }
        if ($value === '' || !validValue($type, $limit, $value)) {
            $errors[] = "Enter a valid value for $label.";
            continue;
        }
        if ($type === 'password') {
            $data['password_hash'] = password_hash($value, PASSWORD_DEFAULT);
        } else {
            $data[$key] = $value;
        }
    }
    if ($table === 'admin_users' && $id === $adminId && ($data['is_active'] ?? '0') !== '1') {
        $errors[] = 'You cannot disable the account you are using.';
    }
    if ($errors) {
        return $errors;
    }
    $keys = array_keys($data);
    try {
        if ($id) {
            $set = implode(', ', array_map(static fn(string $key): string => "`$key` = ?", $keys));
            $stockGuard = $checkStock ? ' AND stock = ?' : '';
            $statement = $db->prepare("UPDATE `$table` SET $set WHERE `$pk` = ?" . $stockGuard);
            $statement->execute($checkStock ? [...array_values($data), $id, $expectedStock] : [...array_values($data), $id]);
            if ($checkStock && $statement->rowCount() === 0 && (string) (findRecord($db, $table, $pk, $id)['stock'] ?? '') !== $expectedStock) {
                return ['Stock changed while this form was open. Reopen the product to see the current inventory.'];
            }
        } else {
            $names = '`' . implode('`, `', $keys) . '`';
            $placeholders = implode(', ', array_fill(0, count($keys), '?'));
            $db->prepare("INSERT INTO `$table` ($names) VALUES ($placeholders)")->execute(array_values($data));
        }
    } catch (PDOException $error) {
        if ($error->getCode() !== '23000') {
            throw $error;
        }
        return ['The record conflicts with an existing unique value or a related record. Check your selections.'];
    }
    return [];
}

function deleteRecord(PDO $db, string $table, string $pk, int $id, int $adminId): string
{
    if ($table === 'admin_users' && $id === $adminId) {
        return 'You cannot delete the account you are using.';
    }
    try {
        $statement = $db->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $statement->execute([$id]);
        return $statement->rowCount() ? 'Record deleted.' : 'The record no longer exists.';
    } catch (PDOException $error) {
        if ($error->getCode() !== '23000') {
            throw $error;
        }
        return 'This record is referenced elsewhere. Change the related records before deleting it.';
    }
}
