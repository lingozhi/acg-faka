# 安装流程调试信息

## 当前修复状态

### 已完成的修复（共7个commit）：

1. **aaaab79** - 将 Lock 文件和 terms 文件移至 /data 持久化目录
2. **f5e20df** - 更新 PHP 版本要求为 8.2+
3. **196777c** - 在 nixpacks 配置中添加 composer 依赖
4. **bb6f119** - 自动创建 /data 持久化目录
5. **b085bb8** - Waf 拦截器跳过 /install 路径的 Lock 检查（**有BUG**）
6. **6301835** - Install 控制器显式排除所有拦截器
7. **ad8378e** - **[关键修复]** Waf 拦截器对 /install 路径直接 return

## 核心问题诊断

### 问题1: Waf.php 逻辑漏洞（已修复 ad8378e）

**之前的错误代码**（commit b085bb8）:
```php
if (!$isInstallRoute && !file_exists(BASE_PATH . '/data/Install.lock')) {
    echo View::render("Rewrite.html");
    exit;
}
// BUG: 这里没有 return！
// 导致 install 路径仍然执行下面的 Firewall::inst()->check()
Firewall::inst()->check(...);
```

**修复后的代码**（commit ad8378e）:
```php
// 如果是安装路径，直接返回，不执行任何检查
if ($isInstallRoute) {
    return;  // ← 关键：提前退出
}

// 如果 Lock 文件不存在，跳转到安装页面
if (!file_exists(BASE_PATH . '/data/Install.lock')) {
    echo View::render("Rewrite.html");
    exit;
}

// 正常的防火墙检查
Firewall::inst()->check(...);
```

## 正确的流程

### 未安装状态访问 /install/step:
1. 请求进入 Kernel.php
2. 创建 /data 目录（如果不存在）
3. 设置 Context::IS_INSTALL = false（因为 Lock 文件不存在）
4. 路由到 Install 控制器
5. Install 控制器使用 `#[Interceptor([])]`，不执行任何拦截器
6. **如果 Waf 被调用**（不应该但万一）:
   - 检测到路由是 `/install/step`
   - `str_starts_with('/install/step', '/install')` = true
   - 直接 return，不做任何事
7. Install::step() 方法执行
8. 检查 Lock 文件是否存在（不存在）
9. 返回 Install.html 模板
10. 显示安装页面 ✅

### 未安装状态访问其他页面:
1. 请求进入 Kernel.php
2. 路由到某个控制器（如 User\Index）
3. 该控制器声明了 `#[Interceptor(Waf::class)]`
4. Waf::handle() 执行:
   - 检测到路由不是 `/install` 开头
   - 检测到 Lock 文件不存在
   - 显示 Rewrite.html
   - Rewrite.html 的 JS 自动 POST `/install/rewrite`
   - 成功后跳转到 `/install/step`

## 可能导致循环的原因

### 如果还在循环，可能是：

1. **Railway 还没部署最新代码**
   - 检查：查看 Railway 日志，确认最新 commit ad8378e 已部署

2. **浏览器缓存**
   - 清除浏览器缓存
   - 强制刷新（Ctrl+Shift+R）

3. **Waf 仍然被调用且逻辑错误**
   - 检查：在 Waf.php 中添加日志
   - 检查：$route 的值是否正确

4. **Install 控制器的拦截器配置不生效**
   - 检查：Interceptor([]) 是否被正确解析

## 下一步调试建议

1. 检查 Railway 部署状态和日志
2. 添加调试日志到 Waf.php 查看执行流程
3. 检查浏览器 Network 面板，查看实际的请求响应
