<?php

namespace Onlyoung4u\AsApi\Middleware;

use Onlyoung4u\AsApi\JwtToken;
use Onlyoung4u\AsApi\Kernel\Exception\JwtTokenException;
use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class Auth implements MiddlewareInterface
{
    use AsResponse;

    public function process(Request $request, callable $next): Response
    {
        try {
            $request->uid = JwtToken::init()->getCurrentId();

            return $next($request);
        } catch (JwtTokenException $exception) {
            return $this->unauthorized($exception->getMessage());
        } catch (\Throwable) {
            return $this->unauthorized();
        }
    }
}