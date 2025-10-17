<?php
declare (strict_types=1);

// 初始化数据库配置（本地开发默认值）
$dbConfig = [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'demo',
    'username' => 'demo',
    'password' => 'TbfXmL2JTcXYYrWZ',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'acg_',
];

// 优先级 1: Railway MySQL 服务环境变量（MYSQL_URL 或单独的变量）
$mysqlUrl = getenv('MYSQL_URL') ?: getenv('MYSQL_PUBLIC_URL');
if ($mysqlUrl) {
    // 使用 MYSQL_URL 解析
    $parsedUrl = parse_url($mysqlUrl);
    if ($parsedUrl) {
        $dbConfig['host'] = $parsedUrl['host'] ?? $dbConfig['host'];
        $dbConfig['database'] = ltrim($parsedUrl['path'] ?? '', '/') ?: $dbConfig['database'];
        $dbConfig['username'] = $parsedUrl['user'] ?? $dbConfig['username'];
        $dbConfig['password'] = $parsedUrl['pass'] ?? $dbConfig['password'];
        if (isset($parsedUrl['port'])) {
            $dbConfig['port'] = $parsedUrl['port'];
        }
    }
} elseif (getenv('MYSQLHOST')) {
    // 使用 Railway 单独的环境变量
    $dbConfig['host'] = getenv('MYSQLHOST');
    $dbConfig['database'] = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: $dbConfig['database'];
    $dbConfig['username'] = getenv('MYSQLUSER') ?: $dbConfig['username'];
    $dbConfig['password'] = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: $dbConfig['password'];
    $dbConfig['port'] = getenv('MYSQLPORT') ?: 3306;
}
// 优先级 2: 通用数据库环境变量（DATABASE_URL）
elseif (getenv('DATABASE_URL')) {
    $parsedUrl = parse_url(getenv('DATABASE_URL'));
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
// 优先级 3: 单独的 DB_* 环境变量
elseif (getenv('DB_HOST')) {
    $dbConfig['host'] = getenv('DB_HOST');
    $dbConfig['database'] = getenv('DB_DATABASE') ?: $dbConfig['database'];
    $dbConfig['username'] = getenv('DB_USERNAME') ?: $dbConfig['username'];
    $dbConfig['password'] = getenv('DB_PASSWORD') ?: $dbConfig['password'];
    if (getenv('DB_PORT')) {
        $dbConfig['port'] = (int)getenv('DB_PORT');
    }
}

return $dbConfig;