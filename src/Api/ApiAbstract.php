<?php


namespace Flooris\FileMakerDataApi\Api;


use Exception;
use Flooris\FileMakerDataApi\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;

abstract class ApiAbstract
{
    const ERROR_INVALID_FILEMAKER_DATA_API_TOKEN = 952;

    /**
     * This will determine if the database will be included in the uri. Most requests will need this, but a few do not.
     *
     * @var bool
     */
    protected bool $includeDatabase = true;

    /**
     * This will determine if the layout will be included in the uri. Most requests will need this, but a few do not.
     *
     * @var bool
     */
    protected bool $includeLayout = true;

    public function __construct(
        public Client   $client,
        private ?string $layoutName = null
    )
    {
    }

    /**
     * @throws Exception // ToDo: Refactor to specific custom exception
     * @throws InvalidArgumentException
     */
    protected function get(string $uri, array $uriValues = [], array $query = []): object
    {
        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionTokenFromCache();

            $response = $this->client->connector->get($preparedUri, $sessionToken, $query);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->get($uri, $uriValues);
        }

        $this->client->setOrExtendSessionToken();

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function post(string $uri, array $uriValues = [], array $parameters = []): object
    {
        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionTokenFromCache();

            $response = $this->client->connector->post($preparedUri, $sessionToken, $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->post($uri, $uriValues, $parameters);
        }

        $this->client->setOrExtendSessionToken();

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function patch(string $uri, array $uriValues = [], array $parameters = []): object
    {
        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionTokenFromCache();

            $response = $this->client->connector->patch($preparedUri, $sessionToken, $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->patch($uri, $uriValues, $parameters)->response;
        }

        $this->client->setOrExtendSessionToken();

        return json_decode($response->getBody(), false)->response;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function delete(string $uri, array $uriValues = []): object
    {
        try {
            $preparedUri  = $this->prepareUri($uri, $uriValues);
            $sessionToken = $this->client->getSessionTokenFromCache();

            $response = $this->client->connector->delete($preparedUri, $sessionToken);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->delete($uri, $uriValues);
        }

        $this->client->setOrExtendSessionToken();

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
        $response     = $e->getResponse();
        $responseData = json_decode($response->getBody());

        if (! isset($responseData->messages)) {
            throw new Exception('Empty response from FileMaker', 0, $e);
        }

        $firstErrorMessage = $responseData->messages[0];

        if ((int)$firstErrorMessage->code === self::ERROR_INVALID_FILEMAKER_DATA_API_TOKEN) {
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