<?php
declare (strict_types=1);

// 解析 Railway DATABASE_URL 环境变量
$databaseUrl = getenv('DATABASE_URL');
$dbConfig = [
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_DATABASE') ?: 'demo',
    'username' => getenv('DB_USERNAME') ?: 'demo',
    'password' => getenv('DB_PASSWORD') ?: 'TbfXmL2JTcXYYrWZ',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'acg_',
];

// 如果存在 DATABASE_URL，解析并使用（Railway 格式）
if ($databaseUrl) {
    $parsedUrl = parse_url($databaseUrl);
    if ($parsedUrl) {
        $dbConfig['host'] = $parsedUrl['host'] ?? $dbConfig['host'];
        $dbConfig['database'] = ltrim($parsedUrl['path'] ?? '', '/') ?: $dbConfig['database'];
        $dbConfig['username'] = $parsedUrl['user'] ?? $dbConfig['username'];
        $dbConfig['password'] = $parsedUrl['pass'] ?? $dbConfig['password'];
        if (isset($parsedUrl['port'])) {
            $dbConfig['port'] = $parsedUrl['port'];
        }
    }
}

return $dbConfig;