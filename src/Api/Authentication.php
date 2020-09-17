<?php

namespace Flooris\FileMakerDataApi\Api;

use Exception;

class Authentication extends ApiAbstract
{
    protected $includeLayout = false;

    /**
     * @return string
     * @throws Exception
     */
    public function login()
    {
        $response     = $this->post('sessions');
        $sessionToken = $response->token;

        cache()->set($this->getCacheKey(), $sessionToken, 60 * 15);

        return $sessionToken;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function logout()
    {
        $this->delete('sessions/%s', [cache($this->getCacheKey())]);

        cache()->forget($this->getCacheKey());

        return true;
    }
}