<?php
/**
 * PHP 内置服务器路由脚本
 * 模拟 Apache .htaccess 伪静态规则
 * .htaccess 规则: RewriteRule ^(.*)$ index.php?s=/$1 [QSA,PT,L]
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file_path = __DIR__ . $uri;

// 如果请求的是真实存在的文件，直接返回（让 PHP 内置服务器处理）
if ($uri !== '/' && is_file($file_path)) {
    return false;
}

// 设置 s 参数（模拟 .htaccess 规则）
// 根路径映射到默认路由，其他路径直接使用
if ($uri === '/') {
    $_GET['s'] = '/user/index/index';
} else {
    $_GET['s'] = $uri;
}

$_SERVER['PATH_INFO'] = $_GET['s'];

// 包含 index.php 处理请求
require __DIR__ . '/index.php';
