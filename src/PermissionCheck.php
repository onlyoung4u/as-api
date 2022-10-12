<?php

namespace Onlyoung4u\AsApi;

class PermissionCheck
{
    private static $instances = [];

    /**
     * 初始化
     *
     * @param string $scope
     * @return Permission
     */
    public static function init(string $scope = 'admin'): Permission
    {
        if (!isset(self::$instances[$scope])) {
            self::$instances[$scope] = new Permission($scope);
        }

        return self::$instances[$scope];
    }
}