<?php
/**
 * PHP 内置服务器路由脚本
 * 模拟 Apache .htaccess 伪静态规则
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file_path = __DIR__ . $uri;

// 如果请求的是真实存在的文件，直接返回（让 PHP 内置服务器处理）
if ($uri !== '/' && is_file($file_path)) {
    return false;
}

// 如果请求的是目录并且存在 index.php，则处理
if (is_dir($file_path) && file_exists($file_path . '/index.php')) {
    $file_path = $file_path . '/index.php';
}

// 否则，重写到 index.php，并设置 s 参数（模拟 .htaccess 规则）
// .htaccess 规则: RewriteRule ^(.*)$ index.php?s=/$1
// 注意：根路径 / 不设置 s 参数，让框架使用默认路由
if ($uri !== '/') {
    $_GET['s'] = $uri;
    $_SERVER['PATH_INFO'] = $uri;
}

// 包含 index.php 处理请求
require __DIR__ . '/index.php';
