<?php

use Onlyoung4u\AsApi\Helpers\Bcrypt;
use Onlyoung4u\AsApi\Kernel\AsValidator;

if (!function_exists('as_bcrypt')) {
    /**
     * Hash the given value against the bcrypt algorithm.
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    function as_bcrypt($value, $options = [])
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
    function as_bcrypt_check($value, $hashedValue)
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
    function as_validate($param, string $rule)
    {
        $v = AsValidator::validate(compact('param'), [
            'param' => $rule,
        ]);

        return !$v->fails();
    }
}
