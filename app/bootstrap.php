<?php
declare(strict_types=1);

const PROJECT_ROOT = __DIR__ . '/..';
const STRICT_SQL_SESSION = "SET SESSION sql_mode = CONCAT_WS(',', NULLIF(@@SESSION.sql_mode, ''), 'STRICT_TRANS_TABLES')";

function database(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $local = __DIR__ . '/config.local.php';
    $config = is_file($local) ? require $local : [];
    $host = getenv('QUEEN_DB_HOST') ?: ($config['host'] ?? '127.0.0.1');
    $port = getenv('QUEEN_DB_PORT') ?: ($config['port'] ?? '3307');
    $name = getenv('QUEEN_DB_NAME') ?: ($config['name'] ?? 'queen_digital_stage');
    $user = getenv('QUEEN_DB_USER') ?: ($config['user'] ?? 'queen_stage_app');
    $pass = getenv('QUEEN_DB_PASSWORD');
    $pdo = connectDatabase($host, (int) $port, $name, $user, $pass === false ? ($config['password'] ?? '') : $pass);
    return $pdo;
}

function connectDatabase(string $host, int $port, string $name, string $user, string $password): PDO
{
    return new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => STRICT_SQL_SESSION,
    ]);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function webUrl(?string $url): string
{
    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }
    return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['https', 'http'], true) ? $url : '';
}

function assetUrl(?string $url): string
{
    if ($url && preg_match('~\Aassets/(?:[a-zA-Z0-9_-]+/)*[a-zA-Z0-9_.-]+\.(?:webp|png|jpe?g|gif|svg|avif)\z~i', $url) && is_file(PROJECT_ROOT . '/' . $url)) {
        return $url;
    }
    return webUrl($url);
}

function youtubeId(?string $value): string
{
    return $value && preg_match('/\A[A-Za-z0-9_-]{11}\z/', $value) ? $value : '';
}

function mediaYoutubeId(?string $url): string
{
    if (!webUrl($url)) {
        return '';
    }
    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    $path = trim($parts['path'] ?? '', '/');
    if ($host === 'youtu.be') {
        return youtubeId($path);
    }
    if (!in_array($host, ['youtube.com', 'www.youtube.com', 'www.youtube-nocookie.com', 'm.youtube.com'], true)) {
        return '';
    }
    parse_str($parts['query'] ?? '', $query);
    $candidate = $query['v'] ?? preg_replace('~^(?:embed|shorts)/~', '', $path);
    return is_string($candidate) ? youtubeId($candidate) : '';
}

function spotifyEmbed(?string $url, string $kind): string
{
    if (!webUrl($url) || strtolower((string) parse_url($url, PHP_URL_HOST)) !== 'open.spotify.com') {
        return '';
    }
    $path = parse_url($url, PHP_URL_PATH) ?? '';
    return preg_match('~\A/(?:embed/)?' . $kind . '/([A-Za-z0-9]{22})/?\z~', $path, $match)
        ? "https://open.spotify.com/embed/$kind/{$match[1]}?theme=0" : '';
}

function startSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    session_name('queen_stage');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function requireCsrf(): void
{
    $token = postText('csrf');
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('This form has expired. Reload the page and try again.');
    }
}

function postText(string $field): string
{
    $value = $_POST[$field] ?? '';
    return is_string($value) ? trim($value) : '';
}

function queryText(string $field): string
{
    $value = $_GET[$field] ?? '';
    return is_string($value) ? trim($value) : '';
}

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

function unavailable(Throwable $error): never
{
    error_log('Queen Digital Stage: ' . $error->getMessage());
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    exit('<!doctype html><html lang="en"><meta charset="UTF-8"><title>Queen Digital Stage</title><h1>Queen Digital Stage</h1><p>The archive is temporarily unavailable. Please try again shortly.</p></html>');
}
