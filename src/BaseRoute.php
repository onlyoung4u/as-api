<?php

namespace Onlyoung4u\AsApi;

use Onlyoung4u\AsApi\Controller\AuthController;
use Onlyoung4u\AsApi\Controller\ConfigController;
use Onlyoung4u\AsApi\Controller\LogsController;
use Onlyoung4u\AsApi\Controller\RoleController;
use Onlyoung4u\AsApi\Controller\UploadController;
use Onlyoung4u\AsApi\Controller\UserController;
use Onlyoung4u\AsApi\Middleware\ActionLog;
use Onlyoung4u\AsApi\Middleware\Auth;
use Onlyoung4u\AsApi\Middleware\Permission;
use Webman\Route;

class BaseRoute
{
    /**
     * 加载基础路由
     *
     * @return void
     */
    public static function load(): void
    {
        /**
         * 跨域
         */
        Route::options('/admin/{path:.+}', function () {
            return response();
        });

        /**
         * 登录
         */
        Route::post('/admin/login', [AuthController::class, 'login'])
            ->name('login')
            ->middleware([
                config('plugin.onlyoung4u.as-api.app.middleware.action_log', ActionLog::class),
            ]);

        /**
         * 公共路由
         */
        Route::group('/admin', function () {
            // 登出
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            // 用户信息
            Route::get('/me', [AuthController::class, 'me'])->name('me');

            // 用户菜单
            Route::get('/menu', [AuthController::class, 'menu'])->name('menu');
            
            // 用户权限
            Route::get('/permissions', [AuthController::class, 'permissions'])->name('permissions');

            // 修改密码
            Route::post('/reset_pwd', [AuthController::class, 'resetPwd'])->name('resetPwd');

            // 配置字典
            Route::get('/config/dict', [ConfigController::class, 'configDict'])->name('config.dict');

            // 通用上传
            Route::post('/upload', [UploadController::class, 'upload'])->name('upload');
            // 分片上传
            Route::post('/upload_slice', [UploadController::class, 'uploadSlice'])->name('upload');
        })->middleware([
            config('plugin.onlyoung4u.as-api.app.middleware.auth', Auth::class),
            config('plugin.onlyoung4u.as-api.app.middleware.action_log', ActionLog::class),
        ]);

        /**
         * 权限路由
         */
        Route::group('/admin', function () {
            // 配置管理
            Route::get('/config', [ConfigController::class, 'configList'])->name('system.config.list');
            Route::post('/config', [ConfigController::class, 'configCreate'])->name('system.config.create');
            Route::post('/config/sort', [ConfigController::class, 'configSort'])->name('system.config.sort');
            Route::put('/config/{id:\d+}', [ConfigController::class, 'configUpdate'])->name('system.config.update');
            Route::delete('/config/{id:\d+}', [ConfigController::class, 'configDel'])->name('system.config.delete');

            // 系统设置
            Route::get('/configs/group', [ConfigController::class, 'configGroup'])->name('system.configs');
            Route::get('/configs', [ConfigController::class, 'configListByGroup'])->name('system.configs');
            Route::post('/configs', [ConfigController::class, 'configBatchStore'])->name('system.configs.store');

            // 角色
            Route::get('/menus', [RoleController::class, 'menuTree'])->name('role.list');
            Route::get('/role', [RoleController::class, 'roleList'])->name('role.list');
            Route::get('/role/{id:\d+}', [RoleController::class, 'roleDetail'])->name('role.list');
            Route::post('/role', [RoleController::class, 'roleCreate'])->name('role.create');
            Route::put('/role/{id:\d+}', [RoleController::class, 'roleUpdate'])->name('role.update');
            Route::delete('/role/{id:\d+}', [RoleController::class, 'roleDel'])->name('role.delete');

            // 用户
            Route::get('/user', [UserController::class, 'userList'])->name('user.list');
            Route::get('/user/roles', [UserController::class, 'userRoles'])->name('user.list');
            Route::post('/user', [UserController::class, 'userCreate'])->name('user.create');
            Route::put('/user/{id:\d+}', [UserController::class, 'userUpdate'])->name('user.update');
            Route::put('/user/{id:\d+}/status', [UserController::class, 'userStatus'])->name('user.update');
            Route::delete('/user/{id:\d+}', [UserController::class, 'userDel'])->name('user.delete');

            // 操作记录
            Route::get('/action_logs', [LogsController::class, 'actionLogs'])->name('actionLogs.list');
            Route::post('/action_logs/clear', [LogsController::class, 'actionLogsClear'])->name('actionLogs.clear');
        })->middleware([
            config('plugin.onlyoung4u.as-api.app.middleware.auth', Auth::class),
            config('plugin.onlyoung4u.as-api.app.middleware.action_log', ActionLog::class),
            config('plugin.onlyoung4u.as-api.app.middleware.permission', Permission::class),
        ]);
    }
}