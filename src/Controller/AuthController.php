<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Helpers\AsConstant;
use Onlyoung4u\AsApi\JwtToken;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\Model\AsMenu;
use Onlyoung4u\AsApi\Model\AsRole;
use Onlyoung4u\AsApi\Model\AsUser;
use Respect\Validation\Validator as v;
use support\Request;
use support\Response;

class AuthController extends Base
{
    /**
     * 登录
     *
     * @param Request $request
     * @return Response
     * @throws AsErrorException
     */
    public function login(Request $request): Response
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $this->validateParams(compact('username', 'password'), [
            'username' => v::stringType()->length(1, 255)->setName('用户名'),
            'password' => v::stringType()->length(1, 255)->setName('密码'),
        ]);

        $user = AsUser::where('username', $username)
            ->where('is_del', AsConstant::STATUS_NO)
            ->first();

        if (!$user) throw new AsErrorException('用户名或密码错误');
        if ($user->status != AsConstant::STATUS_AVAILABLE) throw new AsErrorException('用户已禁用');

        try {
            if (as_bcrypt_check($password, $user->password)) {
                $tokenData = JwtToken::init()->generateToken([JwtToken::EXTEND_ID => $user->id]);

                // 记录登录信息
                $user->last_login_ip = $request->getRealIp();
                $user->last_login_time = time();
                $user->save();

                return $this->success($tokenData);
            }
        } catch (\Throwable) {}

        return $this->error('用户名或密码错误');
    }

    /**
     * 登出
     *
     * @return Response
     */
    public function logout(): Response
    {
        try {
            JwtToken::init()->ban();

            return $this->success();
        } catch (\Throwable) {
            return $this->error();
        }
    }

    /**
     * 用户信息
     *
     * @param Request $request
     * @return Response
     */
    public function me(Request $request): Response
    {
        $uid = $request->uid;

        $user = AsUser::find($uid);

        if ($uid == 1) {
            $roles = AsUser::SUPER_ADMIN_ROLE;
        } else {
            $roleList = AsRole::getUserRoles($uid, 'list', false);

            $roles = [];

            foreach ($roleList as $item) {
                $roles[] = ['roleName' => $item['name'], 'value' => $item['name']];
            }
        }

        $data = [
            'userId' => $user->id,
            'avatar' => $user->avatar,
            'username' => $user->username,
            'realName' => $user->nickname,
            'roles' => $roles,
        ];

        return $this->success($data);
    }

    /**
     * 用户菜单
     *
     * @param Request $request
     * @return Response
     */
    public function menu(Request $request): Response
    {
        $menus = AsMenu::getRoleMenu($request->uid);

        return $this->success($menus);
    }

    /**
     * 用户权限
     *
     * @param Request $request
     * @return Response
     */
    public function permissions(Request $request): Response
    {
        $permissions = AsRole::getUserRules($request->uid);

        return $this->success($permissions);
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return Response
     * @throws AsErrorException
     */
    public function resetPwd(Request $request): Response
    {
        $pwd = $request->input('pwd');
        $password = $request->input('password');

        $this->validateParams(compact('pwd', 'password'), [
            'pwd' => v::stringType()->length(8, 20),
            'password' => v::stringType()->length(8, 20),
        ]);

        $user = AsUser::find($request->uid);

        if (!as_bcrypt_check($pwd, $user->password)) {
            return $this->error('原密码错误');
        }

        $user->password = as_bcrypt($password);

        return $this->successOrError($user->save());
    }
}