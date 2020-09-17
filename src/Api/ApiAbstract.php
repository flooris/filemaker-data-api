<?php


namespace Flooris\FileMakerDataApi\Api;


use Exception;
use Flooris\FileMakerDataApi\Client;
use GuzzleHttp\Exception\GuzzleException;

abstract class ApiAbstract
{
    const ERROR_INVALID_FILEMAKER_DATA_API_TOKEN = 952;

    /**
     * The client instance.
     *
     * @var Client
     */
    private $client;

    /**
     * The layout name.
     *
     * @var string|null
     */
    private $layoutName;

    /**
     * This will determine if the database will be included in the uri. Most requests will need this, but a few do not.
     *
     * @var bool
     */
    protected $includeDatabase = true;

    /**
     * This will determine if the layout will be included in the uri. Most requests will need this, but a few do not.
     *
     * @var bool
     */
    protected $includeLayout = true;

    /**
     * Create a new API instance.
     *
     * @param Client      $client
     * @param string|null $layoutName
     */
    public function __construct(Client $client, $layoutName = null)
    {
        $this->layoutName = $layoutName;
        $this->client     = $client;
    }

    /**
     * @param       $uri
     * @param array $uriValues
     * @param array $query
     * @return object
     * @throws Exception
     */
    protected function get($uri, $uriValues = [], $query = [])
    {
        try {
            $response = $this->client->connector->get($this->prepareUri($uri, $uriValues), $query);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->get($uri, $uriValues);
        }

        $this->extendSessionToken();

        return json_decode($response->getBody())->response;
    }

    /**
     * @param       $uri
     * @param array $uriValues
     * @param array $parameters
     * @return object
     * @throws Exception
     */
    protected function post($uri, $uriValues = [], $parameters = [])
    {
        try {
            $response = $this->client->connector->post($this->prepareUri($uri, $uriValues), $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->post($uri, $uriValues, $parameters);
        }

        $this->extendSessionToken();

        return json_decode($response->getBody())->response;
    }

    /**
     * @param       $uri
     * @param array $uriValues
     * @param array $parameters
     * @return object
     * @throws Exception
     */
    protected function patch($uri, $uriValues = [], $parameters = [])
    {
        try {
            $response = $this->client->connector->patch($this->prepareUri($uri, $uriValues), $parameters);
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->patch($uri, $uriValues, $parameters)->response;
        }

        $this->extendSessionToken();

        return json_decode($response->getBody())->response;
    }

    /**
     * @param       $uri
     * @param array $uriValues
     * @return object
     * @throws Exception
     */
    protected function delete($uri, $uriValues = [])
    {
        try {
            $response = $this->client->connector->delete($this->prepareUri($uri, $uriValues));
        } catch (GuzzleException $e) {
            $this->handleException($e);

            return $this->delete($uri, $uriValues);
        }

        $this->extendSessionToken();

        return json_decode($response->getBody())->response;
    }

    /**
     * @param       $uri
     * @param array $uriValues
     * @return string
     */
    private function prepareUri($uri, $uriValues = [])
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

    /**
     * @return string
     */
    private function getVersionUri()
    {
        return sprintf('/fmi/data/%s/', config(sprintf('filemaker.%s.version', $this->getConfigHost())));
    }

    /**
     * @return string
     */
    private function getDatabaseUri()
    {
        return sprintf('databases/%s/', config(sprintf('filemaker.%s.database', $this->getConfigHost())));
    }

    /**
     * @return string
     */
    private function getLayoutUri()
    {
        return sprintf('layouts/%s/', $this->getLayoutName());
    }

    /**
     * @return string|null
     */
    public function getLayoutName(): ?string
    {
        return $this->layoutName;
    }

    /**
     * @param string|null $layoutName
     */
    public function setLayoutName(?string $layoutName): void
    {
        $this->layoutName = $layoutName;
    }

    /**
     * @param GuzzleException $e
     * @return null
     * @throws Exception
     */
    private function handleException(GuzzleException $e)
    {
        $response     = $e->getResponse();
        $responseData = json_decode($response->getBody());

        if (! isset($responseData->messages)) {
            throw new Exception('Empty response from FileMaker', 0, $e);
        }

        $firstErrorMessage = $responseData->messages[0];

        if ((int)$firstErrorMessage->code === self::ERROR_INVALID_FILEMAKER_DATA_API_TOKEN) {
            cache()->delete(sprintf('filemaker.%s.session_token', $this->getConfigHost()));
            $this->client->validateSession();

            return null;
        }

        throw new Exception($firstErrorMessage->message, $firstErrorMessage->code, $e);
    }

    /**
     * @return mixed|string
     */
    public function getConfigHost()
    {
        return $this->client->configHost;
    }

    public function getCacheKey()
    {
        return sprintf('filemaker.%s.session_token', $this->getConfigHost());
    }

    public function extendSessionToken()
    {
        cache()->set($this->getCacheKey(), cache($this->getCacheKey()), 60 * 15);
    }
}