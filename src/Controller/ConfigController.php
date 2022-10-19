<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Helpers\AsConstant;
use Onlyoung4u\AsApi\Model\AsConfig;
use support\Request;
use support\Response;

class ConfigController extends Base
{
    /**
     * 获取配置字典
     *
     * @return Response
     */
    public function configDict(): Response
    {
        $type = AsConfig::getConstConfig('type');
        $group = AsConfig::getConstConfig('group');

        return $this->success(compact('type', 'group'));
    }

    /**
     * 配置列表
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configList(Request $request): Response
    {
        $params = $this->getPageParams($request);

        $group = $request->input('group');
        $keyword = $request->input('keyword');

        $sql = AsConfig::when(isset(AsConfig::GROUP[$group]), function ($query) use ($group) {
                $query->where('group', $group);
            })
            ->when(!empty($keyword), function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('title', 'like', '%' . $keyword . '%');
                });
            });

        $total = $sql->count();

        $list = $sql->orderBy('sort')
            ->orderBy('id', 'desc')
            ->offset($params['offset'])
            ->limit($params['limit'])
            ->get()
            ->map(function ($item) {
                $options = [];

                if (!empty($item->extra)) {
                    $options = explode(',', $item->extra);
                }

                $item->options = $options;
                $item->value = AsConfig::getValue($item->type, $item->value);

                return $item;
            });

        return $this->success(compact('list', 'total'));
    }

    /**
     * 配置排序
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configSort(Request $request): Response
    {
        $data = $request->input('data');

        $this->validateParamsWithoutErrorMessage(compact('data'), [
            'data' => 'required|array|min:1',
        ]);

        AsConfig::updateSort($data);

        return $this->success();
    }

    /**
     * 配置参数
     *
     * @param Request $request
     * @return array
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    private function configParams(Request $request): array
    {
        $types = implode(',', array_keys(AsConfig::TYPES));
        $groups = implode(',', array_keys(AsConfig::GROUP));

        $data = $this->validateParams($request->all(), [
            'group' => ['required|in:' . $groups, '分组'],
            'type' => ['required|in:' . $types, '类型'],
            'name' => ['required|string|between:1,255', '名称'],
            'key' => ['required|string|between:1,255', '别名'],
            'sort' => ['nullable|integer|min:1', '排序'],
            'status' => ['required|boolean', '状态'],
            'extra' => ['nullable|string|max:255', '选项'],
            'remark' => ['nullable|string|max:255', '备注'],
        ]);

        $data['sort'] = $data['sort'] ?? 1;
        $data['status'] = $data['status'] ? 1 : 0;
        $data['remark'] = $data['remark'] ?? '';

        return $data;
    }

    /**
     * 配置添加
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configCreate(Request $request): Response
    {
        $data = $this->configParams($request);

        AsConfig::store($data);

        return $this->success();
    }

    /**
     * 配置修改
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configUpdate(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        $data = $this->configParams($request);

        AsConfig::store($data, $id);

        return $this->success();
    }

    /**
     * 配置删除
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configDel(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        AsConfig::del($id);

        return $this->success();
    }

    /**
     * 获取已有配置项分组
     *
     * @return Response
     */
    public function configGroup()
    {
        $list = AsConfig::where('status', AsConstant::STATUS_AVAILABLE)
            ->groupBy('group')
            ->pluck('group')
            ->toArray();

        return $this->success($list);
    }

    /**
     * 根据分组获取配置项
     *
     * @param Request $request
     * @return Response
     */
    public function configListByGroup(Request $request)
    {
        $group = $request->input('group');

        if (empty($group)) return $this->errorParam();

        $list = AsConfig::where('status', AsConstant::STATUS_AVAILABLE)
            ->where('group', $group)
            ->get()
            ->map(function ($item) {
                $options = [];

                if (!empty($item->extra)) {
                    $options = explode(',', $item->extra);
                }

                $item->options = $options;
                $item->value = AsConfig::getValue($item->type, $item->value);

                return $item;
            })
            ->toArray();

        return $this->success($list);
    }

    /**
     * 更新配置值
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function configBatchStore(Request $request)
    {
        $data = $request->input('data');

        $this->validateParamsWithoutErrorMessage(compact('data'), [
            'data' => 'required|array|min:1',
        ]);

        AsConfig::updateValue($data);

        return $this->success();
    }
}