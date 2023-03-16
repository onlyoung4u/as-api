<?php

namespace Onlyoung4u\AsApi;

use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\Model\AsRule;
use support\Db;
use Throwable;

class Permission
{
    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    const TYPE_ROLE = 'r';
    const TYPE_USER = 'u';

    /**
     * 作用域
     *
     * @var string
     */
    private string $scope;

    /**
     * 用户角色
     *
     * @var array
     */
    private array $rules = [];

    /**
     * 角色
     *
     * @var array
     */
    private array $roles = [];

    /**
     * 角色和用户绑定关系
     *
     * @var array
     */
    private array $roleBindUsers = [];

    /**
     * 刷新
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->rules = [];
        $this->roles = [];
    }

    /**
     * 删除角色
     *
     * @param int $roleId
     * @return void
     */
    private function deleteRole(int $roleId): void
    {
        AsRule::where('type', self::TYPE_ROLE)
            ->where('v0', $this->scope)
            ->where('v1', $roleId)
            ->delete();
    }

    /**
     * 删除拥有角色的所有用户关联
     *
     * @param int $roleId
     * @return void
     */
    private function deleteRoleRelations(int $roleId): void
    {
        AsRule::where('type', self::TYPE_USER)
            ->where('v0', $this->scope)
            ->where('v2', $roleId)
            ->delete();
    }

    /**
     * 删除用户的所有角色
     *
     * @param int $uid
     * @return void
     */
    private function deleteUserRoles(int $uid): void
    {
        AsRule::where('type', self::TYPE_USER)
            ->where('v0', $this->scope)
            ->where('v1', $uid)
            ->delete();
    }

    /**
     * 添加角色
     *
     * @param int $roleId
     * @param array $permissions
     * @param bool $isEdit
     * @return void
     * @throws AsErrorException
     */
    public function addRole(int $roleId, array $permissions, bool $isEdit = false): void
    {
        Db::beginTransaction();

        try {
            if ($isEdit) $this->deleteRole($roleId);

            $insertData = [];

            foreach ($permissions as $permission) {
                $insertData[] = [
                    'type' => self::TYPE_ROLE,
                    'v0' => $this->scope,
                    'v1' => $roleId,
                    'v2' => $permission,
                ];
            }

            AsRule::insert($insertData);

            Db::commit();

            $this->refresh();
        } catch (Throwable) {
            Db::rollBack();
            throw new AsErrorException();
        }
    }

    /**
     * 编辑角色
     *
     * @param int $roleId
     * @param array $permissions
     * @return void
     * @throws AsErrorException
     */
    public function editRole(int $roleId, array $permissions): void
    {
        $this->addRole($roleId, $permissions, true);
    }

    /**
     * 删除角色
     *
     * @param int $roleId
     * @return void
     * @throws AsErrorException
     */
    public function delRole(int $roleId): void
    {
        Db::beginTransaction();

        try {
            $this->deleteRole($roleId);

            $this->deleteRoleRelations($roleId);

            Db::commit();

            $this->refresh();
        } catch (Throwable) {
            Db::rollBack();
            throw new AsErrorException();
        }
    }

    /**
     * 用户添加角色
     *
     * @param int $uid
     * @param array $roles
     * @return void
     * @throws AsErrorException
     */
    public function userAddRoles(int $uid, array $roles): void
    {
        $this->userBindRoles($uid, $roles, true);
    }

    /**
     * 用户绑定角色
     *
     * @param int $uid
     * @param array $roles
     * @param bool $isAdd
     * @return void
     * @throws AsErrorException
     */
    public function userBindRoles(int $uid, array $roles, bool $isAdd = false): void
    {
        try {
            if ($isAdd) {
                $existRoles = AsRule::where('type', self::TYPE_USER)
                    ->where('v0', $this->scope)
                    ->where('v1', $uid)
                    ->whereIn('v2', $roles)
                    ->pluck('v2')
                    ->toArray();

                if (!empty($existRoles)) {
                    $roles = array_filter($roles, function ($item) use ($existRoles) {
                        return !in_array($item, $existRoles);
                    });
                }
            } else {
                $this->deleteUserRoles($uid);
            }

            $insertData = [];

            if (!empty($roles)) {
                foreach ($roles as $role) {
                    $insertData[] = [
                        'type' => self::TYPE_USER,
                        'v0' => $this->scope,
                        'v1' => $uid,
                        'v2' => $role,
                    ];
                }

                AsRule::insert($insertData);
            }

            $this->refresh();
        } catch (Throwable) {
            throw new AsErrorException();
        }
    }

    /**
     * 获取用户角色
     *
     * @param int $uid
     * @return array
     */
    public function getRules(int $uid = 0): array
    {
        if (empty($this->rules)) {
            $rules = [];

            $list = AsRule::where('type', self::TYPE_USER)
                ->where('v0', $this->scope)
                ->get();

            foreach ($list as $item) {
                $uid = $item->v1;

                if (!isset($rules[$uid])) {
                    $rules[$uid] = [];
                }

                $rules[$uid][] = $item->v2;
            }

            $this->rules = $rules;
        }

        if ($uid == 0) return $this->rules;

        return $this->rules[$uid] ?? [];
    }

    /**
     * 获取角色
     *
     * @param int $roleId
     * @return array
     */
    public function getRoles(int $roleId = 0): array
    {
        if (empty($this->roles)) {
            $roles = [];

            $list = AsRule::where('type', self::TYPE_ROLE)
                ->where('v0', $this->scope)
                ->get();

            foreach ($list as $item) {
                $roleId = $item->v1;

                if (!isset($roles[$roleId])) {
                    $roles[$roleId] = [];
                }

                $roles[$roleId][] = $item->v2;
            }

            $this->roles = $roles;
        }

        if ($roleId == 0) return $this->roles;

        return $this->roles[$roleId] ?? [];
    }

    /**
     * 获取角色绑定的用户
     *
     * @param int $roleId
     * @return array
     */
    public function getRoleBindUsers(int $roleId = 0): array
    {
        if (empty($this->roleBindUsers)) {
            $roleBindUsers = [];

            $list = AsRule::where('type', self::TYPE_USER)
                ->where('v0', $this->scope)
                ->get();

            foreach ($list as $item) {
                $roleId = $item->v2;

                if (!isset($roleBindUsers[$roleId])) {
                    $roleBindUsers[$roleId] = [];
                }

                $roleBindUsers[$roleId][] = $item->v1;
            }
        }

        if ($roleId == 0) return $this->roleBindUsers;

        return $this->roleBindUsers[$roleId] ?? [];
    }

    /**
     * 获取用户权限
     *
     * @param int $uid
     * @return array
     */
    public function getUserPermissions(int $uid): array
    {
        $permissions = [];

        $rules = $this->getRules($uid);

        foreach ($rules as $rule) {
            $permissions = array_merge($permissions, $this->getRoles($rule));
        }

        return array_unique($permissions);
    }

    /**
     * 获取用户是否拥有某项权限
     *
     * @param int $uid
     * @param string $permission
     * @return bool
     */
    public function isUserHasPermission(int $uid, string $permission): bool
    {
        return in_array($permission, $this->getUserPermissions($uid));
    }

    /**
     * 获取用户是否拥有某些权限
     *
     * @param int $uid
     * @param array $permissions
     * @return bool
     */
    public function isUserHasPermissions(int $uid, array $permissions): bool
    {
        $flag = true;

        $allPermissions = $this->getUserPermissions($uid);

        foreach ($permissions as $permission) {
            if (!in_array($permission, $allPermissions)) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }
}