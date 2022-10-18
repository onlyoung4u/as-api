<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Model\AsMenu;
use Onlyoung4u\AsApi\Model\AsRole;
use support\Request;
use support\Response;

class RoleController extends Base
{
    /**
     * 获取用户拥有的菜单树
     *
     * @param Request $request
     * @return Response
     */
    public function menuTree(Request $request): Response
    {
        $tree = AsMenu::getRoleMenu($request->uid, false);

        return $this->success($tree);
    }

    /**
     * 角色列表
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function roleList(Request $request): Response
    {
        $params = $this->getPageParams($request);

        $uid = $request->uid;
        $name = $request->input('name');

        $page = AsRole::with(['owner:id,username,nickname'])
            ->when($uid !== 1, function ($query) use ($uid) {
                $query->where('created_by', $uid);
            })
            ->when(!empty($name), function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->paginate($params['limit'], ['*'], 'page', $params['page']);

        return $this->page($page);
    }

    /**
     * 角色详情
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function roleDetail(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        $detail = AsRole::getDetail($id);

        return $this->success($detail);
    }

    /**
     * 角色参数
     *
     * @param Request $request
     * @return array
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    private function roleParams(Request $request): array
    {
        $name = $request->input('name');
        $permissions = $request->input('permissions');

        $this->validateParams(compact('name', 'permissions'), [
            'name' => ['required|string|between:1,255', '名称'],
            'permissions' => ['required|array|min:1', '权限'],
        ]);

        return [$name, $permissions];
    }

    /**
     * 角色添加
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function roleCreate(Request $request): Response
    {
        [$name, $permissions] = $this->roleParams($request);

        AsRole::store($name, $permissions, $request->uid);

        return $this->success();
    }

    /**
     * 角色修改
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function roleUpdate(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        [$name, $permissions] = $this->roleParams($request);

        AsRole::store($name, $permissions, $request->uid, $id);

        return $this->success();
    }

    /**
     * 角色删除
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function roleDel(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        AsRole::del($id, $request->uid);

        return $this->success();
    }
}