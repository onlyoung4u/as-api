# as-api

## 安装
```shell
composer require -W onlyoung4u/as-api
```

## 环境变量
```shell
cp .env.example .env
```

## 获取环境变量
```php
env('key', 'default')
```

## 开始使用
1. 修改 `config/database.php` 和 `config/redis.php` 配置
2. 执行 `php vendor/bin/phinx migrate -t 20220101000001` 创建表
3. 执行 `php vendor/bin/phinx seed:run -s AsUserSeeder` 初始化用户数据
4. 执行 `php vendor/bin/phinx seed:run -s AsMenuSeeder` 初始化菜单数据
5. 修改 `config/exception.php`
```php
// 增加
'admin' => \Onlyoung4u\AsApi\Kernel\Exception\Handler::class,
'AsApi' => \Onlyoung4u\AsApi\Kernel\Exception\Handler::class,
```

## 迁移命令
```shell
# 创建
php vendor/bin/phinx create MyNewMigration

# 全部迁移
php vendor/bin/phinx migrate
# 指定迁移目标版本
php vendor/bin/phinx migrate -t 20220101000001
```
## 填充命令
```shell
# 创建
php vendor/bin/phinx seed:create MyNewSeeder

# 全部填充
php vendor/bin/phinx seed:run
# 指定填充文件
php vendor/bin/phinx seed:run -s MyNewSeeder
```

## 处理 404
> 在配置文件 config/route.php 里加上如下配置
```php
Route::fallback(function () {
    return json(['code' => 404, 'msg' => '404 not found', 'data' => (object)[]]);
});
```

## 关闭默认路由
> 在配置文件 config/route.php 里最后一行加上如下配置
```php
Route::disableDefaultRoute();
```