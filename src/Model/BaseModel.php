<?php

namespace Onlyoung4u\AsApi\Model;

use Illuminate\Database\Eloquent\Builder;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use support\Db;
use support\Log;
use support\Model;

class BaseModel extends Model
{
    /**
     * 当前登录用户作用域
     *
     * @param Builder $query
     * @param int $uid
     * @param string $uidKey
     * @return Builder
     */
    public function scopeCurrentUser(Builder $query, int $uid = 0, string $uidKey = 'uid')
    {
        if ($uid == 0) {
            $uid = request()->uid ?? 0;
        }

        return $query->where($uidKey, $uid);
    }

    /**
     * 错误处理
     *
     * @param $exception
     * @return false
     * @throws AsErrorException
     */
    public static function handleError($exception): void
    {
        if ($exception instanceof AsErrorException) {
            throw $exception;
        }

        $debug = config('app.debug', false);

        if (!$debug) {
            Log::debug($exception->getMessage(), $exception->getTrace());
        }

        throw new AsErrorException($debug ? $exception->getMessage() : '');
    }

    /**
     * 基础保存
     *
     * @param array $data
     * @param int $id
     * @return void
     * @throws AsErrorException
     */
    public static function baseStore(array $data, int $id = 0): void
    {
        Db::beginTransaction();

        try {
            if ($id > 0) {
                $sql = static::findOrFail($id);
            } else {
                $sql = new static;
            }

            foreach ($data as $key => $value) {
                $sql->setAttribute($key, $value);
            }

            $sql->save();

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();

            static::handleError($exception);
        }
    }

    /**
     * 基础删除
     *
     * @param int $id
     * @return void
     * @throws AsErrorException
     */
    public static function baseDel(int $id): void
    {
        try {
            $sql = self::findOrFail($id);

            $sql->delete();
        } catch (\Throwable $exception) {
            static::handleError($exception);
        }
    }


    /**
     * 当前登录用户保存
     *
     * @param array $data
     * @param int $id
     * @param string $uidKey
     * @return void
     * @throws AsErrorException
     */
    public static function storeByCurrentUser(array $data, int $id = 0, string $uidKey = 'uid'): void
    {
        Db::beginTransaction();

        try {
            $uid = request()->uid ?? 0;

            if ($uid == 0) {
                throw new AsErrorException('获取用户ID失败');
            }

            if ($id > 0) {
                $sql = static::currentUser($uid)->findOrFail($id);
            } else {
                $sql = new static;

                $sql->$uidKey = $uid;
            }

            foreach ($data as $key => $value) {
                $sql->setAttribute($key, $value);
            }

            $sql->save();

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();

            static::handleError($exception);
        }
    }

    /**
     * 当前登录用户删除
     *
     * @param int $id
     * @param string $uidKey
     * @return void
     * @throws AsErrorException
     */
    public static function delByCurrentUser(int $id, string $uidKey = 'uid')
    {
        try {
            $sql = static::currentUser(0, $uidKey)->findOrFail($id);

            $sql->delete();
        } catch (\Throwable $exception) {
            static::handleError($exception);
        }
    }
}