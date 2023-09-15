<?php

use Carbon\Carbon;
use Onlyoung4u\AsApi\Helpers\Bcrypt;
use Onlyoung4u\AsApi\Helpers\AsValidator;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;

if (!function_exists('as_bcrypt')) {
    /**
     * Hash the given value against the bcrypt algorithm.
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    function as_bcrypt(string $value, array $options = []): string
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
    function as_bcrypt_check(string $value, string $hashedValue): bool
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
     * @param $id
     * @return bool
     */
    function as_validate_id($id): bool
    {
        if (!is_string($id) && !is_numeric($id)) return false;
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
    function as_generate_unique_id(string $prefix = ''): string
    {
        return uniqid($prefix . rand(10000, 99999)) . time();
    }
}

if (!function_exists('as_get_file_extension')) {
    /**
     * 根据文件名获取扩展名
     *
     * @param string $fileName
     * @param bool $withDot
     * @return string
     */
    function as_get_file_extension(string $fileName, bool $withDot = true): string
    {
        $arr = explode('.', $fileName);
        $len = count($arr);

        $extension = '';

        if ($len > 1) {
            $extension = ($withDot ? '.' : '') . strtolower($arr[$len - 1]);
        }

        return $extension;
    }
}

if (!function_exists('as_file_date_path')) {
    /**
     * 获取文件日期路径
     *
     * @param string $extension
     * @param string $path
     * @param string $subPath
     * @return string
     */
    function as_file_date_path(string $extension, string $path = '', string $subPath = ''): string
    {
        $datePath = as_path_combine(Carbon::now()->format('Y/m/d/'), $subPath);

        if (!empty($extension)) {
            if (!str_ends_with($datePath, '/')) $datePath .= '/';
            $datePath .= as_generate_unique_id() . '.' . $extension;
        }

        return as_path_combine($path, $datePath);
    }
}

if (!function_exists('as_get_file_url')) {
    /**
     * 获取文件URL
     *
     * @param string $path
     * @return string
     */
    function as_get_file_url(string $path): string
    {
        if (empty($path)) return '';

        if (str_starts_with($path, 'http')) return $path;

        $prefix = config('plugin.onlyoung4u.as-api.app.upload_file.url', 'http://127.0.0.1:8787/');

        return as_path_combine($prefix, $path);
    }
}

if (!function_exists('as_get_file_path')) {
    /**
     * 获取文件路径
     *
     * @param string $url
     * @return string
     */
    function as_get_file_path(string $url): string
    {
        if (empty($url)) return '';

        $prefix = config('plugin.onlyoung4u.as-api.app.upload_file.url', 'http://127.0.0.1:8787/');

        return str_replace($prefix, '', $url);
    }
}

if (!function_exists('as_error_throw')) {
    /**
     * 通用错误抛出
     *
     * @param string $message
     * @param int $code
     * @return void
     * @throws AsErrorException
     */
    function as_error_throw(string $message = '', int $code = 0): void
    {
        throw new AsErrorException($message, $code);
    }
}
