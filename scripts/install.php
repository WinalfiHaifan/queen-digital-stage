<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../app/bootstrap.php';

$configPath = PROJECT_ROOT . '/app/config.local.php';
$config = is_file($configPath) ? require $configPath : null;
$port = (int) (getenv('QUEEN_DB_PORT') ?: ($config['port'] ?? 3307));
$dbName = $config['name'] ?? 'queen_digital_stage';
$appUser = $config['user'] ?? 'queen_stage_app';
$adminName = 'queen_admin';
$localData = getenv('LOCALAPPDATA');
$receipt = getenv('QUEEN_ADMIN_RECEIPT') ?: ($localData ? $localData . '/QueenDigitalStage/admin-login.txt' : '');

if ($port < 1 || $port > 65535 || !preg_match('/\A[a-z0-9_]+\z/', $dbName) || !preg_match('/\A[a-z0-9_]+\z/', $appUser)) {
    fwrite(STDERR, "Invalid database settings.\n");
    exit(1);
}

function requireMatchingLocalConfig(array $config, int $port, string $dbName, string $appUser): void
{
    if ($config['host'] !== '127.0.0.1' || (int) $config['port'] !== $port || $config['name'] !== $dbName || $config['user'] !== $appUser) {
        throw new RuntimeException('Installer settings must match the existing local database configuration.');
    }
}

function writeLocalFile(string $path, string $content): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
        throw new RuntimeException('Cannot create local settings directory.');
    }
    if (file_put_contents($path . '.tmp', $content, LOCK_EX) === false || !rename($path . '.tmp', $path)) {
        throw new RuntimeException('Cannot save local settings.');
    }
}

$server = null;
$locked = false;
$result = 0;
try {
    if ($config) {
        requireMatchingLocalConfig($config, $port, $dbName, $appUser);
    }
    $server = new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4", getenv('QUEEN_INSTALL_USER') ?: 'root', getenv('QUEEN_INSTALL_PASSWORD') ?: '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => STRICT_SQL_SESSION,
    ]);
    $lock = $server->prepare('SELECT GET_LOCK(?, 10)');
    $lock->execute([$dbName . '.install']);
    $locked = (int) $lock->fetchColumn() === 1;
    if (!$locked) {
        throw new RuntimeException('Another installer is running.');
    }
    if (!$config && is_file($configPath)) {
        $config = require $configPath;
        requireMatchingLocalConfig($config, $port, $dbName, $appUser);
    }
    $server->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $server->exec("USE `$dbName`");
    $server->exec(file_get_contents(PROJECT_ROOT . '/database/schema.sql'));
    $server->exec(file_get_contents(PROJECT_ROOT . '/database/checkout.sql'));

    if (!$config) {
        $password = bin2hex(random_bytes(24));
        $account = $server->quote($appUser) . "@'127.0.0.1'";
        // Preserve a pre-existing account if another application already owns it.
        $server->exec("CREATE USER $account IDENTIFIED BY " . $server->quote($password));
        $config = ['host' => '127.0.0.1', 'port' => $port, 'name' => $dbName, 'user' => $appUser, 'password' => $password];
        try {
            $server->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `$dbName`.* TO $account");
            writeLocalFile($configPath, "<?php\nreturn " . var_export($config, true) . ";\n");
        } catch (Throwable $error) {
            $server->exec("DROP USER $account");
            throw $error;
        }
    }

    $db = connectDatabase('127.0.0.1', $port, $dbName, $appUser, $config['password']);
    $contentCount = 0;
    foreach (['band_members', 'albums', 'songs', 'timeline_events', 'media_items', 'product_categories', 'products'] as $table) {
        $contentCount += (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }
    if (!$contentCount) {
        // The seed uses a transaction so a failed import never leaves half a catalog.
        $server->beginTransaction();
        $server->exec(file_get_contents(PROJECT_ROOT . '/database/seed.sql'));
        $server->commit();
        echo "Catalog imported.\n";
    }

    if (!(int) $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn()) {
        $insideWebroot = static function (string $path): bool {
            $dir = realpath(dirname($path));
            $web = realpath(PROJECT_ROOT . '/../..');
            return $dir && $web && str_starts_with(strtolower($dir . DIRECTORY_SEPARATOR), strtolower($web . DIRECTORY_SEPARATOR));
        };
        if (!$receipt || !preg_match('~^(?:[A-Za-z]:[/\\\\]|/)~', $receipt) || str_contains(str_replace('\\', '/', $receipt), '/../') || $insideWebroot($receipt)) {
            throw new RuntimeException('Set QUEEN_ADMIN_RECEIPT to a private absolute path outside htdocs.');
        }
        if (!is_dir(dirname($receipt)) && !mkdir(dirname($receipt), 0700, true)) {
            throw new RuntimeException('Cannot create the credential directory.');
        }
        if ($insideWebroot($receipt) || is_link($receipt)) {
            throw new RuntimeException('The credential file must be outside htdocs.');
        }
        $firstPassword = bin2hex(random_bytes(18));
        writeLocalFile($receipt, "Queen Digital Stage (local admin)\nUsername: $adminName\nPassword: $firstPassword\n");
        $statement = $db->prepare('INSERT INTO admin_users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)');
        $statement->execute([$adminName, 'admin@queen-stage.test', password_hash($firstPassword, PASSWORD_DEFAULT), 'Queen Stage Administrator']);
        echo "Admin credentials saved to $receipt\n";
    }
    echo "Queen Digital Stage database is ready on port $port.\n";
} catch (Throwable $error) {
    if ($server && $server->inTransaction()) {
        $server->rollBack();
    }
    // PDO may repeat a provisioning SQL statement. Keep secrets out of the console.
    $message = $error instanceof PDOException ? 'Database setup failed (SQLSTATE ' . $error->getCode() . '). Check database service, credentials, and schema permissions.' : $error->getMessage();
    fwrite(STDERR, $message . "\n");
    $result = 1;
} finally {
    if ($locked) {
        $release = $server->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$dbName . '.install']);
    }
}
exit($result);
