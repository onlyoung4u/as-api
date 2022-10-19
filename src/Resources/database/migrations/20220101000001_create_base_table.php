<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateBaseTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // 用户
        $user = $this->table('as_users');
        $user->addColumn('username', 'string', ['comment' => '用户名'])
            ->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '状态：0-禁用，1-启用'])
            ->addColumn('password', 'string', ['limit' => 60, 'comment' => '密码'])
            ->addColumn('avatar', 'string', ['default' => '', 'comment' => '头像'])
            ->addColumn('nickname', 'string', ['limit' => 60, 'comment' => '昵称'])
            ->addColumn('created_by', 'integer', ['signed' => false, 'comment' => '创建者'])
            ->addColumn('last_login_ip', 'string', ['default' => '', 'limit' => 15, 'comment' => '最后登录ip'])
            ->addColumn('last_login_time', 'integer', ['default' => 0, 'signed' => false, 'comment' => '最后登录时间'])
            ->addColumn('is_del', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否删除：0-否，1-是'])
            ->addTimestamps()
            ->addIndex(['username'], ['unique' => true])
            ->create();

        // 操作日志
        $userLog = $this->table('as_action_logs');
        $userLog->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否成功'])
            ->addColumn('route_path', 'string', ['comment' => '路由路径'])
            ->addColumn('route_name', 'string', ['comment' => '路由名称'])
            ->addColumn('ip', 'string', ['limit' => 15, 'comment' => '操作ip'])
            ->addColumn('method', 'string', ['limit' => 10, 'comment' => '请求方式'])
            ->addColumn('action_uid', 'integer', ['signed' => false, 'comment' => '操作人'])
            ->addColumn('action_user', 'json', ['comment' => '操作人详细资料'])
            ->addColumn('content', 'json', ['comment' => '操作内容'])
            ->addTimestamps()
            ->create();

        // 菜单
        $menu = $this->table('as_menus');
        $menu->addColumn('key', 'string', ['comment' => '菜单索引'])
            ->addColumn('name', 'string', ['comment' => '菜单名称'])
            ->addColumn('redirect', 'string', ['default' => '', 'comment' => '路由重定向'])
            ->addColumn('title', 'string', ['default' => '', 'comment' => '菜单中文名称'])
            ->addColumn('icon', 'string', ['default' => '', 'comment' => '图标'])
            ->addColumn('hidden', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否隐藏边栏：0-否，1-是'])
            ->addColumn('link', 'string', ['default' => '', 'comment' => '外联地址'])
            ->addColumn('pid', 'integer', ['default' => 0, 'comment' => '上级菜单'])
            ->addColumn('sort', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'comment' => '排序'])
            ->addTimestamps()
            ->addIndex(['key'], ['unique' => true])
            ->create();

        // 角色
        $roles = $this->table('as_roles');
        $roles->addColumn('name', 'string', ['comment' => '角色名称'])
            ->addColumn('created_by', 'integer', ['signed' => false, 'comment' => '角色创建用户ID'])
            ->addTimestamps()
            ->create();

        // 权限
        $rule = $this->table('as_rules');
        $rule->addColumn('type', 'string', ['limit' => 8])
            ->addColumn('v0', 'string', ['default' => ''])
            ->addColumn('v1', 'string', ['default' => ''])
            ->addColumn('v2', 'string', ['default' => ''])
            ->addColumn('v3', 'string', ['default' => ''])
            ->addColumn('v4', 'string', ['default' => ''])
            ->addColumn('v5', 'string', ['default' => ''])
            ->addIndex(['type'])
            ->addIndex(['v0'])
            ->addIndex(['v1'])
            ->addIndex(['v2'])
            ->addIndex(['v3'])
            ->addIndex(['v4'])
            ->addIndex(['v5'])
            ->create();

        // 系统设置
        $systemConfig = $this->table('as_configs');
        $systemConfig->addColumn('group', 'string', ['comment' => '配置分组'])
            ->addColumn('type', 'string', ['comment' => '配置类型'])
            ->addColumn('key', 'string', ['comment' => '配置key'])
            ->addColumn('name', 'string', ['comment' => '配置名称'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => '配置值'])
            ->addColumn('extra', 'string', ['default' => '', 'comment' => '配置额外参数'])
            ->addColumn('remark', 'string', ['default' => '', 'comment' => '配置说明'])
            ->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false,  'comment' => '状态'])
            ->addColumn('sort', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false,  'comment' => '排序'])
            ->addTimestamps()
            ->addIndex(['group'])
            ->addIndex(['type'])
            ->addIndex(['key'], ['unique' => true])
            ->create();
    }
}
