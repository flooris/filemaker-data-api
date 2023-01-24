<?php


namespace Flooris\FileMakerDataApi\HttpClient;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Flooris\FileMakerDataApi\Client as FmClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Flooris\FileMakerDataApi\Exceptions\FilemakerDataApiConfigHostMissingException;
use Flooris\FileMakerDataApi\Exceptions\FilemakerDataApiConfigInvalidConnectionException;

class Connector
{
    private ?string $baseUrl;
    private ?Client $guzzleClient = null;

    public function __construct(
        private string                     $configHost,
        protected CacheRepositoryInterface $cache
    )
    {
        $this->baseUrl = $this->getBaseUri();

        if ($this->baseUrl === null) {
            return;
        }

        $this->guzzleClient = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    public function get(string $uri, ?string $sessionToken = null, array $query = []): ResponseInterface
    {
        return $this->send('GET', $uri, $sessionToken, null, $query);
    }

    public function post(string $uri, ?string $sessionToken = null, mixed $bodyData = null): ResponseInterface
    {
        return $this->send('POST', $uri, $sessionToken, $bodyData);
    }

    public function patch(string $uri, ?string $sessionToken = null, mixed $bodyData = null): ResponseInterface
    {
        return $this->send('PATCH', $uri, $sessionToken, $bodyData);
    }

    public function delete(string $uri, ?string $sessionToken = null): ResponseInterface
    {
        return $this->send('DELETE', $uri, $sessionToken);
    }

    public function getDataContainerToken(string $dataContainerObjectUrl): ?string
    {
        $options = [
            RequestOptions::ALLOW_REDIRECTS => false,
        ];

        try {
            $client = new Client([
                'base_uri' => $dataContainerObjectUrl,
            ]);

            $response     = $client->request('GET', '', $options);
            $cookieHeader = $response->getHeader('Set-Cookie');

            if ($sessionToken = reset($cookieHeader)) {
                return $sessionToken;
            }

            return null;

        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getDataContainerContent(string $dataContainerObjectUrl, string $dataContainerToken): ?StreamInterface
    {
        $options = [
            RequestOptions::HEADERS => [
                'Cookie' => [
                    $dataContainerToken,
                ],
            ],
        ];

        $client = new Client([
            'base_uri' => $dataContainerObjectUrl,
        ]);

        $response = $client->request('GET', '', $options);

        return $response->getBody();
    }


    private function send(string $method, string $uri, ?string $sessionToken = null, mixed $bodyData = null, array $query = []): ResponseInterface
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => $this->getAuthorizationHeaderValue($sessionToken),
                'User-Agent'    => FmClient::USER_AGENT,
            ],
            RequestOptions::QUERY   => $query,
        ];

        if ($bodyData) {
            $options[RequestOptions::JSON] = $bodyData;
        }

        return $this->guzzleClient->request($method, $uri, $options);
    }

    private function getBaseUri(): ?string
    {
        $port     = config(sprintf('filemaker.%s.port', $this->configHost));
        $protocol = config(sprintf('filemaker.%s.protocol', $this->configHost));
        $host     = config(sprintf('filemaker.%s.hostname', $this->configHost));

        if (! $protocol ||
            ! $host
        ) {
            return null;
        }

        return sprintf('%s%s%s/',
            $protocol,
            $host,
            $port ? ":{$port}" : '',
        );
    }

    private function getAuthorizationHeaderValue(?string $sessionToken = null): string
    {
        try {
            if ($sessionToken) {
                return "Bearer {$sessionToken}";
            }

            $username = config(sprintf('filemaker.%s.username', $this->configHost));
            $password = config(sprintf('filemaker.%s.password', $this->configHost));

            return 'Basic ' . base64_encode("{$username}:{$password}");
        } catch (InvalidArgumentException $e) {
            // ToDo: handle exception
        }

        return '';
    }

    public function hasValidConnectionCredentials(): bool
    {
        try {
            $this->evaluateConnectionConfig();
        } catch (FilemakerDataApiConfigInvalidConnectionException|FilemakerDataApiConfigHostMissingException $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws FilemakerDataApiConfigInvalidConnectionException|FilemakerDataApiConfigHostMissingException
     */
    public function evaluateConnectionConfig(bool $throwException = true): void
    {
        $config = config(sprintf('filemaker.%s', $this->configHost));

        if (! $config) {
            throw new FilemakerDataApiConfigHostMissingException($this->configHost);
        }

        $nullableConfigKeys = [
            'port',
        ];

        foreach ($config as $configKey => $configValue) {
            if (in_array($configKey, $nullableConfigKeys)) {
                continue;
            }

            if (! $configValue && $throwException) {
                throw new FilemakerDataApiConfigInvalidConnectionException("The key {$configKey} has no value");
            }
        }
    }
}