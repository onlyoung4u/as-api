<?php

namespace Onlyoung4u\AsApi\Model;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use support\Db;
use support\Redis;

class AsConfig extends BaseModel
{
    const CACHE_KEY = 'as_config_cache';

    const GROUP = [
        'base' => '基础配置',
        'system' => '系统配置',
        'pay' => '支付配置',
        'other' => '其他配置',
    ];

    const TYPES = [
        'number' => '数字',
        'string' => '字符',
        'url' => '链接',
        'textarea' => '文本',
        'password' => '密码',
        'select' => '下拉',
        'radio' => '单选',
        'checkbox' => '多选',
        'image' => '单图',
        'images' => '多图',
        'file' => '单文件',
        'files' => '多文件',
    ];

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 状态访问器
     *
     * @return Attribute
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value == 1,
        );
    }

    /**
     * 获取静态配置项
     *
     * @param string $type
     * @param bool $onlyKey
     * @return array
     */
    public static function getConstConfig(string $type, bool $onlyKey = false): array
    {
        $list = $type == 'group' ? self::GROUP : self::TYPES;

        $data = [];

        foreach ($list as $value => $label) {
            if ($onlyKey) $data[] = $value;
            else $data[] = compact('value', 'label');
        }

        return $data;
    }

    /**
     * 获取配置值
     *
     * @param string $type
     * @param $value
     * @return array|string|null
     */
    public static function getValue(string $type, $value): mixed
    {
        switch ($type) {
            case 'checkbox':
                $res = empty($value) ? [] : json_decode($value, true);
                break;
            case 'image':
            case 'images':
            case 'file':
            case 'files':
                $list = empty($value) ? [] : json_decode($value, true);

                $res = [];

                foreach ($list as $item) {
                    $name = $item['name'];
                    $path = $item['path'];
                    $url = as_get_file_url($path);

                    $res[] = compact('name', 'path', 'url');
                }
                break;
            default:
                $res = $value;
                break;
        }

        return $res;
    }

    /**
     * 格式化保存值
     *
     * @param string $type
     * @param $value
     * @return string
     */
    public static function setValue(string $type, $value): string
    {
        switch ($type) {
            case 'checkbox':
                return json_encode($value ?: [], JSON_UNESCAPED_UNICODE);
            case 'image':
            case 'images':
            case 'file':
            case 'files':
                $list = $value ?: [];

                $data = [];

                foreach ($list as $item) {
                    $data[] = [
                        'name' => $item['name'],
                        'path' => as_get_file_path($item['path'] ?? $item['url'] ?? ''),
                    ];
                }

                return json_encode($data, JSON_UNESCAPED_UNICODE);
            default:
                return $value ?: '';
        }
    }

    /**
     * 更新排序
     *
     * @param array $data
     * @return void
     * @throws AsErrorException
     */
    public static function updateSort(array $data): void
    {
        Db::beginTransaction();

        try {
            foreach ($data as $item) {
                if (isset($item['id']) && isset($item['sort'])) {
                    self::where('id', $item['id'])->update(['sort' => $item['sort']]);
                }
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            self::handleError($exception);
        }
    }

    /**
     * 清除缓存
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Redis::del(self::CACHE_KEY);
    }

    /**
     * 保存
     *
     * @param array $data
     * @param int $id
     * @return void
     * @throws AsErrorException
     */
    public static function store(array $data, int $id = 0): void
    {
        try {
            $isExtra = in_array($data['type'], ['select', 'radio', 'checkbox']);

            if ($isExtra && empty($data['extra'])) {
                throw new AsErrorException('参数错误');
            }

            $count = self::where('id', '<>', $id)
                ->where('key', $data['key'])
                ->count();

            if ($count > 0) {
                throw new AsErrorException('已存在相同配置项');
            }

            if ($id > 0) {
                $config = self::findOrFail($id);

                if ($config->type !== $data['type']) {
                    $config->value = '';
                }
            } else {
                $config = new self;
            }

            foreach ($data as $key => $value) {
                if ($key == 'extra') {
                    $config->$key = $isExtra ? $value : '';
                } else {
                    $config->$key = $value;
                }
            }

            $config->saveOrFail();

            self::clearCache();
        } catch (\Throwable $exception) {
            self::handleError($exception);
        }
    }

    /**
     * 删除
     *
     * @param int $id
     * @return void
     */
    public static function del(int $id): void
    {
        self::where('id', $id)->delete();

        self::clearCache();
    }

    /**
     * 更新配置值
     *
     * @param array $data
     * @return void
     * @throws AsErrorException
     */
    public static function updateValue(array $data): void
    {
        Db::beginTransaction();

        try {
            foreach ($data as $item) {
                if (isset($item['id']) &&
                    as_validate_id($item['id']) &&
                    isset($item['type']) &&
                    isset(self::TYPES[$item['type']])
                ) {
                    $value = self::setValue($item['type'], $item['value'] ?? null);

                    self::where('id', $item['id'])->update(['value' => $value]);
                }
            }

            Db::commit();

            self::clearCache();
        } catch (\Throwable $exception) {
            Db::rollBack();
            self::handleError($exception);
        }
    }

    /**
     * 获取配置
     *
     * @param string $key
     * @return mixed|null
     */
    public static function getConfig(string $key): mixed
    {
        if (empty($key)) return null;

        $cache = Redis::get(self::CACHE_KEY);

        if (!$cache) {
            $list = self::select(['id', 'key', 'name', 'type', 'value'])
                ->get()
                ->map(function ($item) {
                    $item->value = self::getValue($item->type, $item->value);

                    return $item;
                })
                ->toArray();

            $config = [];

            foreach ($list as $item) {
                $config[$item['key']] = $item;
            }

            Redis::set(self::CACHE_KEY, json_encode($config, JSON_UNESCAPED_UNICODE));
        } else {
            $config = json_decode($cache, true);
        }

        return $config[$key] ?? null;
    }

    /**
     * 获取配置值
     *
     * @param string $key
     * @return array|mixed|string|null
     */
    public static function getConfigValue(string $key): mixed
    {
        $config = self::getConfig($key);

        if (empty($config)) return null;

        $value = $config['value'];

        if ($config['type'] == 'image' || $config['type'] == 'file') {
            if (!empty($value)) {
                return $value[0]['url'];
            }

            return '';
        } else if ($config['type'] == 'images' || $config['type'] == 'files') {
            $list = [];

            foreach ($value as $item) {
                $list[] = $item['url'];
            }

            return $list;
        } else {
            return $value;
        }
    }
}