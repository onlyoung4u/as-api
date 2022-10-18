<?php
return [
    'enable' => true,

    'max_page_size' => 100,
    'code_adapter' => \Onlyoung4u\AsApi\Kernel\AsCode::class,

    'middleware' => [
        'auth' => \Onlyoung4u\AsApi\Middleware\Auth::class,
        'action_log' => \Onlyoung4u\AsApi\Middleware\ActionLog::class,
        'permission' => \Onlyoung4u\AsApi\Middleware\Permission::class,
    ],

    'cors' => [
        'origin' => '*',
        'methods' => 'GET,POST,PUT,DELETE,OPTIONS',
        'headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin',
    ],

    'jwt' => [
        'default' => [
            // 算法类型
            'alg' => 'HS256',
            // 令牌签发者
            'iss' => 'onlyoung4u.as',
            // 是否开启单点登录
            'is_single_sign_in' => false,
            // access 令牌秘钥
            'access_secret_key' => env('JWT_ACCESS_SECRET_KEY'),
            // access 令牌过期时间，单位秒
            'access_exp' => env('JWT_ACCESS_EXP', 7200),
            // refresh 令牌秘钥
            'refresh_secret_key' => env('JWT_REFRESH_SECRET_KEY'),
            // refresh 令牌过期时间，单位秒
            'refresh_exp' => env('JWT_REFRESH_EXP', 604800),
            // access 令牌私钥
            'access_private_key' => '',
            // access 令牌公钥
            'access_public_key' => '',
            // refresh 令牌私钥
            'refresh_private_key' => '',
            // refresh 令牌公钥
            'refresh_public_key' => '',
        ],
    ],
];