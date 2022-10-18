<?php

namespace Onlyoung4u\AsApi\Model;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Onlyoung4u\AsApi\Helpers\AsConstant;
use Onlyoung4u\AsApi\JwtToken;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\Kernel\Exception\AsUnauthorizedException;
use Onlyoung4u\AsApi\PermissionCheck;
use support\Db;
use Throwable;

class AsUser extends BaseModel
{
    const SUPER_ADMIN_ROLE = [
        ['roleName' => '超级管理员', 'value' => '超级管理员'],
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = ['password'];

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
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
     * 获取当前登录用户ID
     *
     * @param bool $safeMode
     * @return int
     * @throws AsUnauthorizedException
     */
    public static function getCurrentUserId(bool $safeMode = true): int
    {
        try {
            return JwtToken::init()->getCurrentId();
        } catch (Throwable) {
            if ($safeMode) throw new AsUnauthorizedException();

            return 0;
        }
    }

    /**
     * 保存
     *
     * @param array $data
     * @param int $uid
     * @param int $id
     * @return void
     * @throws AsErrorException
     */
    public static function store(array $data, int $uid, int $id = 0): void
    {
        Db::beginTransaction();

        try {
            if ($id == 1) throw new AsErrorException();

            if (self::where('id', '<>', $id)->where('username', $data['username'])->count() > 0) {
                throw new AsErrorException('用户名已存在');
            }

            if (!AsRole::checkUserRoles($uid, $data['roles'])) {
                throw new AsErrorException('越级赋权');
            }

            if ($id > 0) {
                $sql = self::when($uid != 1, function ($query) use ($uid) {
                        $query->where('created_by', $uid);
                    })
                    ->findOrFail($id);
            } else {
                $sql = new self;

                $sql->created_by = $uid;
            }

            foreach ($data as $key => $value) {
                if ($key == 'password' || $key == 'roles') continue;
                $sql->$key = $value;
            }

            // 密码
            if (isset($data['password'])) {
                $sql->password = as_bcrypt($data['password']);
            }

            $sql->save();

            // 赋权
            PermissionCheck::init()->userBindRoles($sql->id, $data['roles']);

            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();

            self::handleError($exception);
        }
    }

    /**
     * 停用/启用
     *
     * @param int $id
     * @param bool $type
     * @param int $uid
     * @return void
     * @throws AsErrorException
     */
    public static function setStatus(int $id, bool $type, int $uid): void
    {
        try {
            if ($id == 1) throw new AsErrorException();

            $sql = self::when($uid != 1, function ($query) use ($uid) {
                    $query->where('created_by', $uid);
                })
                ->findOrFail($id);

            $sql->status = $type ? AsConstant::STATUS_AVAILABLE : AsConstant::STATUS_DISABLED;

            $sql->saveOrFail();
        } catch (Throwable $exception) {
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
            if ($id == 1) throw new AsErrorException();

            $sql = self::when($uid != 1, function ($query) use ($uid) {
                    $query->where('created_by', $uid);
                })
                ->findOrFail($id);

            $sql->is_del = AsConstant::STATUS_YES;
            $sql->username = $sql->username . '/Delete@' . Carbon::now()->timestamp;

            $sql->saveOrFail();
        } catch (Throwable $exception) {
            self::handleError($exception);
        }
    }
}