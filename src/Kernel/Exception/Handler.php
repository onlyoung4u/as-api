<?php

namespace Onlyoung4u\AsApi\Kernel\Exception;

use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Throwable;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

class Handler extends ExceptionHandler
{
    use AsResponse;

    public $dontReport = [
        AsErrorException::class,
        AsUnauthorizedException::class,
    ];

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof AsUnauthorizedException) {
            return $this->unauthorized();
        }

        if ($exception instanceof AsErrorException) {
            return $this->errorWithCode($exception->getCode(), $exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}