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