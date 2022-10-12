<?php

namespace Onlyoung4u\AsApi\Middleware;

use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Onlyoung4u\AsApi\PermissionCheck;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class Permission implements MiddlewareInterface
{
    use AsResponse;

    public function process(Request $request, callable $next): Response
    {
        $uid = $request->uid;

        if ($uid == 1) return $next($request);

        if (!PermissionCheck::init()->isUserHasPermission($uid, $request->route->getName())) {
            return $this->accessDenied();
        }

        return $next($request);
    }
}