<?php

namespace Onlyoung4u\AsApi\Controller;

use Onlyoung4u\AsApi\Model\AsActionLog;
use Onlyoung4u\AsApi\Model\AsMenu;
use Onlyoung4u\AsApi\Model\AsUser;
use support\Request;
use support\Response;

class LogsController extends Base
{
    /**
     * 操作记录列表
     *
     * @param Request $request
     * @return Response
     * @throws \Onlyoung4u\AsApi\Kernel\Exception\AsErrorException
     */
    public function actionLogs(Request $request): Response
    {
        $params = $this->getPageParams($request);

        $uid = $request->uid;
        $userId = $request->input('uid');

        $ids = [];

        if ($uid != 1) {
            $ids = AsUser::where('id', $uid)
                ->orWhere('created_by', $uid)
                ->pluck('id')
                ->toArray();
        }

        $sql = AsActionLog::with('operator:id,username,nickname')
            ->when($uid != 1, function ($query) use ($ids) {
                $query->whereIn('action_uid', $ids);
            })
            ->when(!empty($userId), function ($query) use ($userId) {
                $query->where('action_uid', $userId);
            });

        $total = $sql->count();

        $list = $sql->offset($params['offset'])
            ->limit($params['limit'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                if (empty($item->operator)) {
                    $item->operator = json_decode($item->action_user, true);
                }

                $item->full_path = implode('/', AsMenu::getFullPathName($item->route_name));

                return $item;
            });

        return $this->success(compact('list', 'total'));
    }

    /**
     * 操作记录清空
     *
     * @param Request $request
     * @return Response
     */
    public function actionLogsClear(Request $request): Response
    {
        if ($request->uid == 1) {
            AsActionLog::truncate();

            return $this->success();
        }

        return $this->error();
    }
}