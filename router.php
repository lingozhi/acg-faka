<?php
/**
 * PHP 内置服务器路由脚本
 * 模拟 Apache .htaccess 伪静态规则
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 如果请求的是真实存在的文件或目录，直接返回
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// 否则，重写到 index.php，并设置 s 参数（模拟 .htaccess 规则）
$_GET['s'] = $uri;
$_SERVER['PATH_INFO'] = $uri;
$_SERVER['SCRIPT_NAME'] = '/index.php';

// 包含 index.php 处理请求
require __DIR__ . '/index.php';
