<?php


namespace Flooris\FileMakerDataApi\Api;


use Exception;

class Script extends ApiAbstract
{
    /**
     * @param string $scriptName
     * @param string $scriptParameter
     * @return object
     * @throws Exception
     */
    public function runScript($scriptName, $scriptParameter = '')
    {
        return $this->get('script/%s', [$scriptName], ['script.param' => $scriptParameter]);
    }
}