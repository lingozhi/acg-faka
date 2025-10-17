<?php
// 调试文件：检查数据库配置
const BASE_PATH = __DIR__;
require(BASE_PATH . '/vendor/autoload.php');
require("kernel/Helper.php");

$db_config = config('database');

echo "<h2>数据库配置信息：</h2>";
echo "<pre>";
echo "Host: " . ($db_config['host'] ?? 'NOT SET') . "\n";
echo "Port: " . ($db_config['port'] ?? 'NOT SET') . "\n";
echo "Database: " . ($db_config['database'] ?? 'NOT SET') . "\n";
echo "Username: " . ($db_config['username'] ?? 'NOT SET') . "\n";
echo "Password: " . (isset($db_config['password']) ? str_repeat('*', strlen($db_config['password'])) : 'NOT SET') . "\n";
echo "Prefix: " . ($db_config['prefix'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<h2>环境变量：</h2>";
echo "<pre>";
echo "MYSQL_URL: " . (getenv('MYSQL_URL') ? 'SET' : 'NOT SET') . "\n";
echo "MYSQL_PUBLIC_URL: " . (getenv('MYSQL_PUBLIC_URL') ? 'SET' : 'NOT SET') . "\n";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'NOT SET') . "\n";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'NOT SET') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'NOT SET') . "\n";
echo "</pre>";

echo "<h2>数据库连接测试：</h2>";
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4";
    if (isset($db_config['port'])) {
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset=utf8mb4";
    }
    echo "<pre>DSN: $dsn</pre>";

    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "<p style='color:green;'>✓ 数据库连接成功！</p>";

    $prefix = $db_config['prefix'] ?? '';
    $manageTable = $prefix . 'manage';

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$manageTable}`");
    $result = $stmt->fetch();

    echo "<p>manage 表记录数: {$result['count']}</p>";

    if ($result['count'] > 0) {
        echo "<p style='color:green;'>✓ 检测到已安装（manage 表有 {$result['count']} 条记录）</p>";
    } else {
        echo "<p style='color:orange;'>⚠ manage 表为空，系统未安装</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>✗ 数据库连接失败：" . htmlspecialchars($e->getMessage()) . "</p>";
}
