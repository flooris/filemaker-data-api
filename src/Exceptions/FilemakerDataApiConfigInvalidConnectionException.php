<?php

namespace Flooris\FileMakerDataApi\Exceptions;

use Exception;

class FilemakerDataApiConfigInvalidConnectionException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
