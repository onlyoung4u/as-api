<?php

namespace Onlyoung4u\AsApi\Middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class CORS implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        $response = $request->method() == 'OPTIONS' ? response() : $next($request);

        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => config('plugin.onlyoung4u.as-api.app.cors.origin', '*'),
            'Access-Control-Allow-Methods' => config('plugin.onlyoung4u.as-api.app.cors.methods', '*'),
            'Access-Control-Allow-Headers' => config('plugin.onlyoung4u.as-api.app.cors.headers', '*'),
        ]);

        return $response;
    }
}