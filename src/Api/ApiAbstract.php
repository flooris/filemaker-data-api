<?php

namespace Flooris\FileMakerDataApi\Api;

use Exception;
use Flooris\FileMakerDataApi\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\SimpleCache\InvalidArgumentException;
use Flooris\FileMakerDataApi\FileMakerDataApi;

abstract class ApiAbstract
{
    const ERROR_INVALID_FILEMAKER_DATA_API_TOKEN = 952;

    /**
     * This will determine if the database will be included in the uri. Most requests will need this, but a few do not.
     */
    protected bool $includeDatabase = true;

    /**
     * This will determine if the layout will be included in the uri. Most requests will need this, but a few do not.
     */
    protected bool $includeLayout = true;

    public function __construct(
        public FileMakerDataApi $client,
        private ?string $layoutName = null
    ) {
    }

    /**
     * @throws Exception                // ToDo: Refactor to specific custom exception
     * @throws InvalidArgumentException
     */
    protected function get(string $uri, array $uriValues = [], array $query = [], bool $validateSession = true): object
    {
        $sessionToken = null;

        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionToken($validateSession);

            $response = $this->client->connector->get($preparedUri, $sessionToken, $query);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->get($uri, $uriValues);
        }

        $this->client->setOrExtendSessionToken($sessionToken);

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function post(string $uri, array $uriValues = [], array $parameters = [], bool $validateSession = true): object
    {
        $sessionToken = null;

        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionToken($validateSession);

            $response = $this->client->connector->post($preparedUri, $sessionToken, $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->post($uri, $uriValues, $parameters, false);
        }

        $this->client->setOrExtendSessionToken($sessionToken);

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function patch(string $uri, array $uriValues = [], array $parameters = [], bool $validateSession = true): object
    {
        $sessionToken = null;

        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionToken($validateSession);

            $response = $this->client->connector->patch($preparedUri, $sessionToken, $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->patch($uri, $uriValues, $parameters, false)->response;
        }

        $this->client->setOrExtendSessionToken($sessionToken);

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function delete(string $uri, array $uriValues = [], bool $validateSession = true): object
    {
        $sessionToken = null;

        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionToken($validateSession);

            $response = $this->client->connector->delete($preparedUri, $sessionToken);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->delete($uri, $uriValues, false);
        }

        $this->client->setOrExtendSessionToken($sessionToken);

        return json_decode($response->getBody(), false)->response;
    }

    private function prepareUri(string $uri, array $uriValues = []): string
    {
        $filledUri = $uri;
        foreach ($uriValues as $value) {
            $filledUri = sprintf($filledUri, $value);
        }

        return sprintf('%s%s%s%s',
            $this->getVersionUri(),
            $this->includeDatabase ? $this->getDatabaseUri() : '',
            $this->includeLayout ? $this->getLayoutUri() : '',
            $filledUri);
    }

    private function getVersionUri(): string
    {
        return sprintf('/fmi/data/%s/', config(sprintf('filemaker.%s.version', $this->getConfigHost())));
    }

    private function getDatabaseUri(): string
    {
        return sprintf('databases/%s/', config(sprintf('filemaker.%s.database', $this->getConfigHost())));
    }

    private function getLayoutUri(): string
    {
        return sprintf('layouts/%s/', $this->getLayoutName());
    }

    public function getLayoutName(): ?string
    {
        return $this->layoutName;
    }

    public function setLayoutName(?string $layoutName): void
    {
        $this->layoutName = $layoutName;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function handleException(GuzzleException $e): void
    {
        $response     = null;
        $responseData = null;

        if ($e instanceof RequestException) {
            $response     = $e->getResponse();
            $responseData = json_decode($response->getBody());
        }

        if (! isset($responseData->messages)) {
            throw new Exception('Empty response from FileMaker', 0, $e);
        }

        $firstErrorMessage = $responseData->messages[0];

        if ((int) $firstErrorMessage->code === self::ERROR_INVALID_FILEMAKER_DATA_API_TOKEN) {
            $this->client->deleteSessionToken();
            $this->client->validateSession();

            return;
        }

        throw new Exception($firstErrorMessage->message, $firstErrorMessage->code, $e);
    }

    public function getConfigHost(): string
    {
        return $this->client->configHost;
    }
}
