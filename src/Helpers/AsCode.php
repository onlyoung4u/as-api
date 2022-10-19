<?php

namespace Onlyoung4u\AsApi\Helpers;

class AsCode
{
    const STATUS_OK = 0;    // 操作成功
    const STATUS_ERROR = 1;    // 通用错误
    const STATUS_ERROR_PARAM = 2;    // 参数错误

    const STATUS_UNAUTHORIZED = 1000;   // 未登录
    const STATUS_PERMISSION_DENIED = 1001;   // 无权限

    const STATUS_MAP = [
        // 通用
        self::STATUS_OK => '操作成功',
        self::STATUS_ERROR => '操作失败',
        self::STATUS_ERROR_PARAM => '参数错误',

        // 权限相关
        self::STATUS_UNAUTHORIZED => '未登录或登录过期,请重新登录',
        self::STATUS_PERMISSION_DENIED => '无权限',
    ];

    /**
     * 获取状态码对应状态
     *
     * @param $code
     * @param string $msg
     * @return string
     */
    public static function getStatusText($code, string $msg = '') {
        if (!empty($msg)) return $msg;
        return self::STATUS_MAP[$code] ?? '';
    }
}