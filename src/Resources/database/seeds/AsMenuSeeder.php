<?php


use Phinx\Seed\AbstractSeed;

class AsMenuSeeder extends AbstractSeed
{
    public function getActions(string $name, int $pid, array $keys = ['create', 'update', 'delete']): array
    {
        $actions = [
            'create' => '添加',
            'update' => '编辑',
            'delete' => '删除',
        ];

        $data = [];

        foreach ($keys as $key) {
            $title = $actions[$key] ?? '';

            if (empty($title)) continue;

            $data[] = [
                'key' => $name . '.' . $key,
                'name' => $key,
                'title' => $title,
                'hidden' => 1,
                'pid' => $pid,
            ];
        }

        return $data;
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $table = $this->table('as_menus');

        // 清空表
        $table->truncate();

        // 管理员
        $data = [
            'key' => 'user',
            'name' => 'user',
            'title' => '管理员',
            'redirect' => '/users/list',
            'icon' => 'ant-design:user-outlined',
            'pid' => 0,
            'sort' => 99,
        ];

        $table->insert($data)->saveData();

        $pid = $this->getAdapter()->getConnection()->lastInsertId();

        // 角色管理
        $data = [
            'key' => 'role.list',
            'name' => 'role',
            'title' => '角色管理',
            'pid' => $pid,
        ];

        $table->insert($data)->saveData();

        $data = $this->getActions('role', $this->getAdapter()->getConnection()->lastInsertId());

        $table->insert($data)->saveData();

        // 管理员管理
        $data = [
            'key' => 'user.list',
            'name' => 'user',
            'title' => '管理员管理',
            'pid' => $pid,
        ];

        $table->insert($data)->saveData();

        $data = $this->getActions('user', $this->getAdapter()->getConnection()->lastInsertId());

        $table->insert($data)->saveData();

        // 操作记录
        $data = [
            'key' => 'actionLogs.list',
            'name' => 'logs',
            'title' => '操作日志',
            'redirect' => '/logs/index',
            'icon' => 'akar-icons:eye-closed',
            'pid' => 0,
            'sort' => 99,
        ];

        $table->insert($data)->saveData();

        $data = [
            'key' => 'actionLogs.clear',
            'name' => 'clear',
            'title' => '清空日志',
            'hidden' => 1,
            'pid' => $this->getAdapter()->getConnection()->lastInsertId(),
        ];

        $table->insert($data)->saveData();
    }
}
