<?php

namespace Flooris\FileMakerDataApi\Api;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class Script extends ApiAbstract
{
    /**
     * @throws Exception|InvalidArgumentException
     */
    public function runScript(string $scriptName, string $scriptParameter = ''): object
    {
        return $this->get('script/%s', [$scriptName], ['script.param' => $scriptParameter]);
    }
}
