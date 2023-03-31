<?php

namespace Onlyoung4u\AsApi;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Onlyoung4u\AsApi\Kernel\Exception\JwtConfigException;
use Onlyoung4u\AsApi\Kernel\Exception\JwtTokenException;
use support\Redis;
use Throwable;

class JwtToken
{
    private const ACCESS_TOKEN = 1;
    private const REFRESH_TOKEN = 2;

    const EXTEND_ID = 'id';

    const CACHE_TOKEN_PREFIX = 'as_token_';
    const CACHE_BLACKLIST_TOKEN_PREFIX = 'as_blacklist_token_';

    private string $type = '';
    private array $config = [];

    /**
     * 处理配置文件
     *
     * @param string $type
     * @return array
     * @throws JwtConfigException
     */
    private static function handleConfig(string $type): array
    {
        $config = config('plugin.onlyoung4u.as-api.app.jwt.' . $type);

        if (empty($config)) {
            throw new JwtConfigException('jwt配置文件不存在');
        }

        $alg = $config['alg'] ?? '';

        if (!in_array($alg, ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'EdDSA'])) {
            throw new JwtConfigException('jwt配置错误，不支持的加密算法:' . $alg);
        }

        $keys = ['access_secret_key', 'refresh_secret_key'];

        if (str_starts_with($alg, 'RS') || str_starts_with($alg, 'ES')) {
            $keys = ['access_private_key', 'access_public_key', 'refresh_private_key', 'refresh_public_key'];
        }

        foreach ($keys as $key) {
            if (empty($config[$key])) {
                throw new JwtConfigException('jwt配置错误，缺少:' . $key);
            }
        }

        $defaultKeys = [
            'access_exp' => 7200,
            'refresh_exp' => 604800,
            'iss' => 'onlyoung4u.as',
            'is_single_sign_in' => false,
        ];

        foreach ($defaultKeys as $key => $value) {
            if (empty($config[$key])) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    /**
     * 初始化
     *
     * @param string $type
     * @return JwtToken
     * @throws JwtConfigException
     */
    public static function init(string $type = 'default'): JwtToken
    {
        $instance = new self;

        $instance->type = $type;
        $instance->config = self::handleConfig($type);

        return $instance;
    }

    /**
     * 生成载荷
     *
     * @param array $extend
     * @return array
     */
    private function generatePayload(array $extend): array
    {
        $config = $this->config;

        $now = time();

        $basePayload = [
            'iss' => $config['iss'],
            'iat' => $now,
            'extend' => $extend,
        ];

        $access = $basePayload;
        $refresh = $basePayload;

        $access['exp'] = $now + $config['access_exp'];
        $refresh['exp'] = $now + $config['refresh_exp'];

        return compact('access', 'refresh');
    }

    /**
     * 获取私钥
     *
     * @param array $config
     * @param int $tokenType
     * @return string
     */
    private function getPrivateKey(array $config, int $tokenType = self::ACCESS_TOKEN): string
    {
        switch ($config['alg']) {
            case 'ES256':
            case 'ES384':
            case 'RS256':
            case 'RS384':
            case 'RS512':
                $key = self::ACCESS_TOKEN == $tokenType ? $config['access_private_key'] : $config['refresh_private_key'];
                break;
            default:
                $key = self::ACCESS_TOKEN == $tokenType ? $config['access_secret_key'] : $config['refresh_secret_key'];
        }

        return $key;
    }

    /**
     * 获取公钥
     *
     * @param array $config
     * @param int $tokenType
     * @return string
     */
    private function getPublicKey(array $config, int $tokenType = self::ACCESS_TOKEN): string
    {
        switch ($config['alg']) {
            case 'ES256':
            case 'ES384':
            case 'RS256':
            case 'RS384':
            case 'RS512':
                $key = self::ACCESS_TOKEN == $tokenType ? $config['access_public_key'] : $config['refresh_public_key'];
                break;
            default:
                $key = self::ACCESS_TOKEN == $tokenType ? $config['access_secret_key'] : $config['refresh_secret_key'];
        }

        return $key;
    }

    /**
     * 获取缓存 key
     *
     * @param $value
     * @param string $type
     * @return string
     */
    private function getCacheKey($value, string $type = 'token'): string
    {
        $prefix = $type == 'token' ? self::CACHE_TOKEN_PREFIX : self::CACHE_BLACKLIST_TOKEN_PREFIX;

        return $prefix . $this->type . '_' . $value;
    }

    /**
     * 生成 token
     *
     * @param array $extend
     * @return array
     */
    public function generateToken(array $extend): array
    {
        if (!isset($extend[self::EXTEND_ID])) {
            throw new JwtTokenException('缺少必要字段:' . self::EXTEND_ID);
        }

        $config = $this->config;

        $payload = $this->generatePayload($extend);

        $secretKey = $this->getPrivateKey($config);
        $refreshSecretKey = $this->getPrivateKey($config, self::REFRESH_TOKEN);

        $token = [
            'token_type' => 'Bearer',
            'expires_in' => $payload['access']['exp'],
            'access_token' => JWT::encode($payload['access'], $secretKey, $config['alg']),
            'refresh_token' => JWT::encode($payload['refresh'], $refreshSecretKey, $config['alg']),
        ];

        // 单点登录
        if ($config['is_single_sign_in']) {
            Redis::set($this->getCacheKey($extend[self::EXTEND_ID]), $token['access_token']);
        }

        return $token;
    }

    /**
     * 从请求中获取 token
     *
     * @return string
     * @throws JwtTokenException
     */
    public static function getTokenFromRequest(): string
    {
        $authorization = request()->header('authorization');

        if (empty($authorization)) {
            throw new JwtTokenException('请求未携带身份令牌');
        }

        if (!str_starts_with($authorization, 'Bearer ')) {
            throw new JwtTokenException('请求未携带身份令牌');
        }

        $token = trim(str_ireplace('bearer', '', $authorization));

        if (empty($token)) {
            throw new JwtTokenException('请求未携带身份令牌');
        }

        return $token;
    }

    /**
     * 解密 token
     *
     * @param string $token
     * @param int $tokenType
     * @return array
     * @throws JwtTokenException
     */
    private function verifyToken(string $token, int $tokenType = self::ACCESS_TOKEN): array
    {
        $config = $this->config;

        $publicKey = $this->getPublicKey($config, $tokenType);

        $decoded = JWT::decode($token, new Key($publicKey, $config['alg']));
        $decoded = json_decode(json_encode($decoded), true);

        if ($tokenType == self::REFRESH_TOKEN) return $decoded;

        // 主动禁用
        if (Redis::get($this->getCacheKey($token, 'blacklist'))) {
            throw new JwtTokenException('身份令牌验证无效');
        }

        // 单点登录
        if ($config['is_single_sign_in']) {
            $id = $decoded['extend'][self::EXTEND_ID];

            $cacheToken = Redis::get($this->getCacheKey($id));

            if ($cacheToken && $cacheToken != $token) {
                throw new JwtTokenException('账号已在其他设备登录');
            }
        }

        return $decoded;
    }

    /**
     * 校验 token
     *
     * @param int $tokenType
     * @param string|null $token
     * @return array
     * @throws JwtTokenException
     */
    public function verify(int $tokenType = self::ACCESS_TOKEN, string $token = null): array
    {
        $token = $token ?? self::getTokenFromRequest();
        $tip = $tokenType == self::ACCESS_TOKEN ? '身份令牌' : '刷新令牌';

        try {
            return $this->verifyToken($token, $tokenType);
        } catch (ExpiredException) {
            throw new JwtTokenException($tip . '已过期');
        } catch (JwtTokenException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new JwtTokenException($tip . '验证无效');
        }
    }

    /**
     * 刷新 token
     *
     * @return array
     * @throws JwtTokenException
     */
    public function refreshToken(): array
    {
        $config = $this->config;
        $token = self::getTokenFromRequest();

        $refreshPayload = $this->verify(self::REFRESH_TOKEN, $token);

        $payload = $this->generatePayload($refreshPayload['extend']);

        $secretKey = $this->getPrivateKey($config);

        return [
            'token_type' => 'Bearer',
            'expires_in' => $payload['access']['exp'],
            'refresh_expires_in' => $refreshPayload['exp'],
            'access_token' => JWT::encode($payload['access'], $secretKey, $config['alg']),
            'refresh_token' => $token,
        ];
    }

    /**
     * 禁用 token
     *
     * @param string|null $token
     * @return void
     * @throws JwtTokenException
     */
    public function ban(string $token = null): void
    {
        $token = $token ?? self::getTokenFromRequest();

        $tokenData = $this->verifyToken($token);

        $id = $tokenData['extend'][self::EXTEND_ID];

        Redis::setEx($this->getCacheKey($token, 'blacklist'), $tokenData['exp'] - time(), $id);

        // 单点登录
        if ($this->config['is_single_sign_in']) {
            Redis::del($this->getCacheKey($id));
        }
    }

    /**
     * 获取当前登录ID
     *
     * @param string|null $token
     * @return int
     * @throws JwtTokenException
     */
    public function getCurrentId(string $token = null): int
    {
        $token = $token ?? self::getTokenFromRequest();

        $tokenData = $this->verify(self::ACCESS_TOKEN, $token);

        return (int)$tokenData['extend'][self::EXTEND_ID];
    }
}