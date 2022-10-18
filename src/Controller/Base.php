<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Onlyoung4u\AsApi\Kernel\AsValidator;
use support\Request;

class Base
{
    use AsResponse;

    /**
     * 验证参数
     *
     * @param array $params
     * @param array $rules
     * @param bool $withError
     * @return array
     * @throws AsErrorException
     */
    protected function validateParams(array $params, array $rules, bool $withError = true): array
    {
        try {
            $v = AsValidator::asValidate($params, $rules);

            if ($v->fails()) {
                $msg = $withError ? $v->errors()->first() : '';
                throw new AsErrorException($msg, $this->CodeAdapter()::STATUS_ERROR_PARAM);
            }

            return $v->validated();
        } catch (AsErrorException $exception) {
            throw $exception;
        } catch (\Throwable) {
            throw new AsErrorException('', $this->CodeAdapter()::STATUS_ERROR_PARAM);
        }
    }

    /**
     * 验证ID
     *
     * @param $id
     * @param bool $withResponse
     * @return bool
     * @throws AsErrorException
     */
    protected function validateId($id, bool $withResponse = false): bool
    {
        $res = as_validate($id, 'required|integer|min:1');

        if (!$res && $withResponse) {
            throw new AsErrorException('', $this->CodeAdapter()::STATUS_ERROR_PARAM);
        }

        return $res;
    }

    /**
     * 验证ID并抛出错误
     *
     * @param $id
     * @return void
     * @throws AsErrorException
     */
    protected function validateIdWithResponse($id): void
    {
        $this->validateId($id, true);
    }

    /**
     * 获取分页大小
     *
     * @param Request $request
     * @return int
     * @throws AsErrorException
     */
    protected function getPageSize(Request $request): int
    {
        $limit = $request->input('limit', 20);

        if (!$this->validateId($limit)) throw new AsErrorException('分页参数错误');

        $maxPageSize = config('plugin.onlyoung4u.as-api.app.max_page_size', 100);

        if ($limit > $maxPageSize) throw new AsErrorException('每页最多' . $maxPageSize . '条');

        return $limit;
    }

    /**
     * 获取分页参数
     *
     * @param Request $request
     * @return array
     * @throws AsErrorException
     */
    protected function getPageParams(Request $request): array
    {
        $page = $request->input('page', 1);

        if (!$this->validateId($page)) $page = 1;

        $limit = $this->getPageSize($request);

        $offset = ($page - 1) * $limit;

        return compact('page', 'limit', 'offset');
    }
}