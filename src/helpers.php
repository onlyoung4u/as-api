<?php

use Carbon\Carbon;
use Onlyoung4u\AsApi\Helpers\Bcrypt;
use Onlyoung4u\AsApi\Helpers\AsValidator;

if (!function_exists('as_bcrypt')) {
    /**
     * Hash the given value against the bcrypt algorithm.
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    function as_bcrypt($value, $options = []): string
    {
        $client = new Bcrypt();

        return $client->make($value, $options);
    }
}

if (!function_exists('as_bcrypt_check')) {
    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    function as_bcrypt_check($value, $hashedValue): bool
    {
        $client = new Bcrypt();

        return $client->check($value, $hashedValue);
    }
}

if (!function_exists('as_validate')) {
    /**
     * 单个参数验证
     *
     * @param $param
     * @param string $rule
     * @return bool
     */
    function as_validate($param, string $rule): bool
    {
        $v = AsValidator::validate(compact('param'), [
            'param' => $rule,
        ]);

        return !$v->fails();
    }
}

if (!function_exists('as_validate_id')) {
    /**
     * 正整数参数验证
     *
     * @param $param
     * @param string $rule
     * @return bool
     */
    function as_validate_id($id): bool
    {
        if (!is_string($id) || !is_numeric($id)) return false;
        return preg_match('/^[1-9][0-9]*$/', $id) === 1;
    }
}

if (!function_exists('as_path_combine')) {
    /**
     * 路径拼接
     *
     * @param string $base
     * @param string $path
     * @return string
     */
    function as_path_combine(string $base, string $path = ''): string
    {
        $res = $base;

        if (!empty($path)) {
            if (!str_ends_with($base, '/')) $res .= '/';

            $res .= ltrim($path, '/');
        }

        return $res;
    }
}

if (!function_exists('as_generate_unique_id')) {
    /**
     * 生成唯一ID
     *
     * @param string $prefix
     * @return string
     */
    function as_generate_unique_id(string $prefix = '') {
        return uniqid($prefix) . time();
    }
}

if (!function_exists('as_get_file_extension')) {
    /**
     * 根据文件名获取扩展名
     *
     * @param string $fileName
     * @return string
     */
    function as_get_file_extension(string $fileName) {
        $arr = explode('.', $fileName);
        $len = count($arr);

        $extension = '';
        if ($len > 1) $extension = '.' . $arr[$len - 1];

        return $extension;
    }
}

if (!function_exists('as_file_date_path')) {
    /**
     * 获取文件日期路径
     *
     * @param string $extension
     * @param string $path
     * @return string
     */
    function as_file_date_path(string $extension, string $path = ''): string
    {
        $datePath = Carbon::now()->format('Y/m/d/') . as_generate_unique_id() . '.' . $extension;

        return as_path_combine($path, $datePath);
    }
}
