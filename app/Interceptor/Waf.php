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
        // 获取当前路由，如果是 install 路径则跳过检查
        $route = \Kernel\Util\Context::get(\Kernel\Consts\Base::ROUTE);
        $isInstallRoute = str_starts_with($route, '/install');

        // 如果不是安装路径，且 Lock 文件不存在，则跳转到安装页面
        if (!$isInstallRoute && !file_exists(BASE_PATH . '/data/Install.lock')) {
            echo View::render("Rewrite.html");
            exit;
        }

        Firewall::inst()->check(function (array $message) {
            hook(\App\Consts\Hook::WAF_INTERCEPT, $message);
            throw new JSONException("The current session is not secure. Please refresh the web page and try again.");
        });
    }
}