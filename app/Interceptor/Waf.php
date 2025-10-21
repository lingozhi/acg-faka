<?php
declare(strict_types=1);

namespace App\Interceptor;


use Kernel\Annotation\InterceptorInterface;
use Kernel\Exception\JSONException;
use Kernel\Util\View;
use Kernel\Waf\Firewall;

class Waf implements InterceptorInterface
{


    /**
     * @param int $type
     * @return void
     * @throws JSONException
     * @throws \SmartyException
     */
    public function handle(int $type): void
    {
        // 获取当前路由，如果是 install 路径则完全跳过所有检查
        $route = (string)\Kernel\Util\Context::get(\Kernel\Consts\Base::ROUTE);
        $isInstallRoute = str_starts_with($route, '/install');

        // DEBUG: 输出调试信息
        error_log("[WAF DEBUG] Route: {$route}, isInstallRoute: " . ($isInstallRoute ? 'true' : 'false'));

        // 如果是安装路径，直接返回，不执行任何检查
        if ($isInstallRoute) {
            error_log("[WAF DEBUG] Skipping all checks for install route");
            return;
        }

        // 如果 Lock 文件不存在，跳转到安装页面
        if (!file_exists(BASE_PATH . '/data/Install.lock')) {
            if ($this->isStaticAsset($route)) {
                error_log("[WAF DEBUG] Allowing asset during install: {$route}");
                return;
            }
            error_log("[WAF DEBUG] Lock file not found, showing Rewrite.html");
            echo View::render("Rewrite.html");
            exit;
        }

        // 正常的防火墙检查
        error_log("[WAF DEBUG] Executing Firewall check");
        Firewall::inst()->check(function (array $message) {
            hook(\App\Consts\Hook::WAF_INTERCEPT, $message);
            throw new JSONException("The current session is not secure. Please refresh the web page and try again.");
        });
    }

    /**
     * 允许安装阶段访问静态资源，否则安装页会因依赖的静态文件被拦截而不断重定向。
     * @param string $route
     * @return bool
     */
    private function isStaticAsset(string $route): bool
    {
        if ($route === '' || $route === '/') {
            return false;
        }

        $path = explode('?', $route, 2)[0];

        $publicPrefixes = [
            '/assets/',
            '/static/',
            '/public/',
        ];

        foreach ($publicPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === '') {
            return false;
        }

        $allowedExtensions = [
            'js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico',
            'woff', 'woff2', 'ttf', 'otf', 'eot', 'json', 'map',
        ];

        return in_array($extension, $allowedExtensions, true);
    }
}
