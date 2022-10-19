<?php

namespace Onlyoung4u\AsApi\Model;

use Onlyoung4u\AsApi\PermissionCheck;
use support\Redis;

class AsMenu extends BaseModel
{
    const CACHE_MENU_KEY = 'as_admin_menus';

    const STATIC_MENU = [
        'login' => '登录',
        'logout' => '登出',
        'upload' => '上传',
        'resetPwd' => '修改密码',
    ];

    const DASHBOARD = [
        [
            'path' => '/',
            'component' => 'LAYOUT',
            'redirect' => '/dashboard',
            'meta' => [
                'title' => '首页',
                'icon' => 'bx:bx-home',
                'affix' => true,
                'hideChildrenInMenu' => true,
            ],
            'children' => [
                [
                    'path' => 'dashboard',
                    'name' => 'Dashboard',
                    'component' => '/dashboard/workbench/index',
                    'meta' => [
                        'title' => '首页',
                        'hideMenu' => true,
                        'currentActiveMenu' => '/'
                    ],
                ]
            ]
        ]
    ];

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 处理菜单结构
     *
     * @param array $line
     * @param string $prefix
     * @return array
     */
    private static function handleLine(array $line, string $prefix): array
    {
        $meta = [
            'title' => $line['title'],
            'hideMenu' => $line['hidden'] == 1,
        ];

        if (!empty($line['icon'])) $meta['icon'] = $line['icon'];

        $isRoot = $prefix == '/';

        return [
            'path' => ($isRoot ? '/' : '') . $line['name'],
            'name' => $line['key'],
            'component' => $isRoot ? 'LAYOUT' : ($prefix . $line['name']),
            'redirect' => $line['redirect'],
            'meta' => $meta,
        ];
    }

    /**
     * 获取树形结构
     *
     * @param array $list
     * @param bool $withHandle
     * @param int $pid
     * @param string $prefix
     * @return array
     */
    public static function getTree(array $list, bool $withHandle = true, int $pid = 0, string $prefix = '/'): array
    {
        $tree = [];

        foreach ($list as $item) {
            if ($item['pid'] == $pid && (!$withHandle || ($withHandle && $item['hidden'] == 0))) {
                $children = self::getTree($list, $withHandle, $item['id'], $prefix . $item['name'] . '/');

                $data = $withHandle ? self::handleLine($item, $prefix) : $item;

                if (!empty($children)) {
                    $data['children'] = $children;
                } else if ($withHandle && $pid == 0) {
                    $data['meta']['hideChildrenInMenu'] = true;

                    $data['children'] = [
                        [
                            'path' => 'index',
                            'name' => $item['key'] . '.index',
                            'component' => $prefix . $item['name'] . '/index',
                            'meta' => [
                                'title' => $item['title'],
                                'hideMenu' => true,
                            ]
                        ]
                    ];
                }

                $tree[] = $data;
            }
        }

        return $tree;
    }

    /**
     * 获取用户菜单
     *
     * @param int $uid
     * @param bool $withHandle
     * @return array|array[]
     */
    public static function getRoleMenu(int $uid, bool $withHandle = true): array
    {
        if ($uid == 1) {
            $menus = self::orderBy('sort')->orderBy('id')->get()->toArray();
        } else {
            $permissions = PermissionCheck::init()->getUserPermissions($uid);

            $menus = self::whereIn('key', $permissions)->orderBy('sort')->orderBy('id')->get()->toArray();
        }

        $tree = self::getTree($menus, $withHandle);

        if (!$withHandle) return $tree;

        return array_merge(self::DASHBOARD, $tree);
    }

    /**
     * 删除缓存
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Redis::del(self::CACHE_MENU_KEY);
    }

    /**
     * 获取全部菜单
     *
     * @param string $key
     * @return array
     */
    public static function getAllMenu(string $key): array
    {
        $menus = Redis::get(self::CACHE_MENU_KEY);

        if (!$menus) {
            $list = self::orderBy('sort')
                ->orderBy('id')
                ->get()
                ->toArray();

            $k = [];
            $id = [];
            $parent = [];
            $children = [];

            foreach ($list as $item) {
                if ($item['pid'] == 0) {
                    $parent[$item['id']] = $item;
                } else {
                    $children[$item['pid']][] = $item;
                }

                $k[$item['key']] = $item;
                $id[$item['id']] = $item;
            }

            $menus = compact('k', 'id', 'parent', 'children');

            Redis::set(self::CACHE_MENU_KEY, json_encode($menus));
        } else {
            $menus = json_decode($menus, true);
        }

        return empty($key) ? $menus : $menus[$key];
    }

    /**
     * 获取所有父节点名称
     *
     * @param int $id
     * @return array
     */
    private static function getParentPathName(int $id): array
    {
        $path = [];

        $ids = self::getAllMenu('id');

        if (isset($ids[$id])) {
            $item = $ids[$id];

            $path[] = $item['title'];

            if ($item['pid'] !== 0) {
                $parent = self::getParentPathName($item['pid']);
                $path = array_merge($path, $parent);
            }
        }

        return $path;
    }

    /**
     * 获取菜单名称
     *
     * @param string $key
     * @param array $extendMenus
     * @return array
     */
    public static function getFullPathName(string $key, array $extendMenus = []): array
    {
        $path = [];

        $keys = self::getAllMenu('k');

        $extendMenus = array_merge(self::STATIC_MENU, $extendMenus);

        if (isset($keys[$key])) {
            $item = $keys[$key];

            $path[] = $item['title'];

            if ($item['pid'] !== 0) {
                $parent = self::getParentPathName($item['pid']);
                $path = array_merge($path, $parent);
            }

            $path = array_reverse($path);
        } else if (isset($extendMenus[$key])) {
            $path[] = $extendMenus[$key];
        }

        return $path;
    }
}