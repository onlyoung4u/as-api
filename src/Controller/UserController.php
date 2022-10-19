<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Helpers\AsConstant;
use Onlyoung4u\AsApi\Model\AsRole;
use Onlyoung4u\AsApi\Model\AsUser;
use support\Request;
use support\Response;

class UserController extends Base
{
    /**
     * 用户列表
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function userList(Request $request): Response
    {
        $params = $this->getPageParams($request);

        $uid = $request->uid;
        $id = $request->input('id');
        $username = $request->input('username');
        $nickname = $request->input('nickname');

        $sql = AsUser::with(['owner:id,username,nickname'])
            ->where('id', '>', 1)
            ->where('is_del', AsConstant::STATUS_NO)
            ->when($uid > 1, function ($query) use ($uid) {
                $query->where('created_by', $uid);
            })
            ->when(as_validate_id($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->when(as_validate($username, 'required|string|between:1,60'), function ($query) use ($username) {
                $query->where('username', 'like', '%' . $username . '%');
            })
            ->when(as_validate($nickname, 'required|string|between:1,60'), function ($query) use ($nickname) {
                $query->where('nickname', 'like', '%' . $nickname . '%');
            });

        $total = $sql->count();

        $list = $sql->offset($params['offset'])
            ->limit($params['limit'])
            ->get()
            ->map(function ($item) {
                $item->roles = AsRole::getUserRoles($item->id, 'list', false);

                return $item;
            });

        return $this->success(compact('list', 'total'));
    }

    /**
     * 用户拥有和创建的角色
     *
     * @param Request $request
     * @return Response
     */
    public function userRoles(Request$request): Response
    {
        $list = AsRole::getUserRoles($request->uid, 'list');

        return $this->success($list);
    }

    /**
     * 用户参数校验
     *
     * @param Request $request
     * @param bool $isUpdate
     * @return array
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    private function userParams(Request $request, bool $isUpdate = false): array
    {
        $passwordRule = $isUpdate ? ['nullable|string|between:8,20', '密码'] : ['required|string|between:8,20', '密码'];

        $data = $this->validateParams($request->all(), [
            'username' => ['required|string|between:4,255', '账号'],
            'nickname' => ['required|string|between:1,255', '名称'],
            'password' => $passwordRule,
            'status' => ['required|boolean', '状态'],
            'roles' => ['required|array|min:1', '角色'],
        ]);

        $data['status'] = $data['status'] ? 1 : 0;

        return $data;
    }

    /**
     * 用户添加
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function userCreate(Request $request): Response
    {
        $data = $this->userParams($request);

        AsUser::store($data, $request->uid);

        return $this->success();
    }

    /**
     * 用户修改
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function userUpdate(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        $data = $this->userParams($request);

        AsUser::store($data, $request->uid, $id);

        return $this->success();
    }

    /**
     * 用户停用/启用
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function userStatus(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        $type = $request->input('type');

        $this->validateParams(compact('type'), [
            'type' => ['required|boolean', '类型'],
        ]);

        AsUser::setStatus($id, $type, $request->uid);

        return $this->success();
    }

    /**
     * 用户删除
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function userDel(Request $request, int $id): Response
    {
        $this->validateIdWithResponse($id);

        AsUser::del($id, $request->uid);

        return $this->success();
    }
}