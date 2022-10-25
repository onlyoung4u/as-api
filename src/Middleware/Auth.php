<?php

namespace Onlyoung4u\AsApi\Middleware;

use Onlyoung4u\AsApi\JwtToken;
use Onlyoung4u\AsApi\Kernel\Exception\AsErrorException;
use Onlyoung4u\AsApi\Kernel\Exception\JwtTokenException;
use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Onlyoung4u\AsApi\Model\AsUser;
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

            $request->user = AsUser::getUserDetail($request->uid);

            return $next($request);
        } catch (AsErrorException $exception) {
            return $this->unauthorized($exception->getMessage());
        } catch (JwtTokenException $exception) {
            return $this->unauthorized($exception->getMessage());
        } catch (\Throwable) {
            return $this->unauthorized();
        }
    }
}