<?php

namespace Flooris\FileMakerDataApi\Api;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class Authentication extends ApiAbstract
{
    protected bool $includeLayout = false;

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function login(): string
    {
        $response     = $this->post('sessions');
        $sessionToken = $response->token;

        $this->client->setOrExtendSessionToken($sessionToken);

        return $sessionToken;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function logout(): bool
    {
        $this->delete('sessions/%s', [$this->client->getSessionTokenFromCache()]);

        $this->client->deleteSessionToken();

        return true;
    }
}