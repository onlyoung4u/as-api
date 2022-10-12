<?php

namespace Onlyoung4u\AsApi\Kernel\Exception;

use Exception;
use Onlyoung4u\AsApi\Kernel\Traits\AsResponse;
use Throwable;

class AsErrorException extends Exception
{
    use AsResponse;

    public function __construct(string $message = '操作失败', int $code = 0, Throwable $previous = null)
    {
        if ($code == 0) $code = $this->CodeAdapter()::STATUS_ERROR;

        parent::__construct($message, $code, $previous);
    }
}