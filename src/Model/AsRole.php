<?php

namespace Onlyoung4u\AsApi\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\PermissionCheck;

class AsRole extends BaseModel
{
    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 创建者
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(AsUser::class, 'created_by');
    }

    /**
     * 获取用户权限
     *
     * @param int $uid
     * @return array
     */
    public static function getUserRules(int $uid): array
    {
        if ($uid == 1) {
            return AsMenu::orderBy('pid')->orderBy('sort')->pluck('key')->toArray();
        }

        return PermissionCheck::init()->getUserPermissions($uid);
    }

    /**
     * 获取用户创建和拥有的角色
     *
     * @param int $uid
     * @param string $type
     * @param bool $isAll
     * @return array
     */
    public static function getUserRoles(int $uid, string $type = 'id', bool $isAll = true): array
    {
        if ($uid == 1) {
            if ($type === 'id') {
                return self::pluck('id')->toArray();
            } else {
                return self::select(['id', 'name'])->get()->toArray();
            }
        }

        $ownIds = PermissionCheck::init()->getRules($uid);

        if ($type === 'id') {
            if (!$isAll) return $ownIds;

            $createIds = self::where('created_by', $uid)
                ->pluck('id')
                ->toArray();

            return array_merge($ownIds, $createIds);
        } else {
            $ownRoles = self::whereIn('id', $ownIds)
                ->get(['id', 'name'])
                ->toArray();

            if (!$isAll) return $ownRoles;

            $createRoles = self::where('created_by', $uid)
                ->get(['id', 'name'])
                ->toArray();

            return array_merge($ownRoles, $createRoles);
        }
    }

    /**
     * 检查用户是否有角色
     *
     * @param int $uid
     * @param array $roles
     * @return bool
     */
    public static function checkUserRoles(int $uid, array $roles): bool
    {
        if (empty($roles)) return true;

        $allRoles = self::getUserRoles($uid);

        foreach ($roles as $role) {
            if (!in_array($role, $allRoles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 角色详情
     *
     * @param int $id
     * @return array
     * @throws AsErrorException
     */
    public static function getDetail(int $id): array
    {
        try {
            $role = self::findOrFail($id);

            $role->permissions = PermissionCheck::init()->getRoles($id);
        } catch (\Throwable $exception) {
            self::handleError($exception);
        }

        return $role->toArray();
    }

    /**
     * 保存
     *
     * @param string $name
     * @param array $permissions
     * @param int $uid
     * @param int $id
     * @return void
     * @throws AsErrorException
     */
    public static function store(string $name, array $permissions, int $uid, int $id = 0): void
    {
        try {
            if ($uid > 1 && !PermissionCheck::init()->isUserHasPermissions($uid, $permissions)) {
                throw new AsErrorException('越级赋权');
            }

            if ($id == 0) {
                $role = new self;
            } else {
                $role = self::findOrFail($id);
            }

            $role->name = $name;
            $role->created_by = $uid;

            $role->saveOrFail();

            PermissionCheck::init()->addRole($role->id, $permissions, $id > 0);
        } catch (\Throwable $exception)  {
            self::handleError($exception);
        }
    }

    /**
     * 删除
     *
     * @param int $id
     * @param int $uid
     * @return void
     * @throws AsErrorException
     */
    public static function del(int $id, int $uid): void
    {
        try {
            $users = PermissionCheck::init()->getRoleBindUsers($id);

            if (!empty($users)) {
                throw new AsErrorException('当前角色已分配给用户，无法删除');
            }

            $role = self::findOrFail($id);

            if ($uid != 1 && $role->created_by != $uid) {
                throw new AsErrorException('只能删除自己创建的角色');
            }

            $role->delete();

            PermissionCheck::init()->delRole($id);
        } catch (\Throwable $exception) {
            self::handleError($exception);
        }
    }
}