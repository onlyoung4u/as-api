<?php

namespace Onlyoung4u\AsApi\Middleware;

use Onlyoung4u\AsApi\Model\AsActionLog;
use Onlyoung4u\AsApi\Model\AsUser;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use support\Container;

class ActionLog implements MiddlewareInterface
{
    private $codeAdapter = null;

    private function getCodeAdapter()
    {
        if (!$this->codeAdapter) {
            $this->codeAdapter = Container::get(config('plugin.onlyoung4u.as-api.app.code_adapter'));
        }

        return $this->codeAdapter;
    }

    public function process(Request $request, callable $next): Response
    {
        $response = $next($request);

        if ($request->method() !== 'GET') {
            $log = new AsActionLog();

            // 用户信息
            $userInfo = '{}';
            $uid = AsUser::getCurrentUserId(false);

            if ($uid > 0) {
                $user = AsUser::find($uid);

                if ($user) {
                    $userInfo = json_encode([
                        'id' => $user->id,
                        'username' => $user->username,
                        'nickname' => $user->nickname,
                    ]);
                }
            }

            // 是否成功
            $status = 0;

            try {
                $body = $response->rawBody();

                $data = json_decode($body, true);

                if (isset($data['code']) && $data['code'] == $this->getCodeAdapter()::STATUS_OK) {
                    $status = 1;
                }
            } catch (\Throwable) {}

            $content = $request->all();

            $log->status = $status;
            $log->route_path = $request->path();
            $log->route_name = $request->route->getName();
            $log->ip = $request->getRealIp();
            $log->method = $request->method();
            $log->action_uid = $uid;
            $log->action_user = $userInfo;
            $log->content = empty($content) ? '{}' : json_encode($content);

            $log->save();
        }

        return $response;
    }
}