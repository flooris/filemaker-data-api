<?php


namespace Flooris\FileMakerDataApi\HttpClient;

use Flooris\FileMakerDataApi\Client as FmClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;

class Connector
{
    /** @var string */
    private string $configHost;
    private string $baseUrl;
    private ?Client $guzzleClient = null;

    public function __construct(string $configHost)
    {
        $this->configHost   = $configHost;
        $this->baseUrl      = $this->getBaseUri();

        if (! $this->baseUrl) {
            return;
        }

        $this->guzzleClient = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    public function get($uri, $query = [])
    {
        return $this->send('GET', $uri, null, $query);
    }

    public function post($uri, $bodyData)
    {
        return $this->send('POST', $uri, $bodyData);
    }

    public function patch($uri, $bodyData)
    {
        return $this->send('PATCH', $uri, $bodyData);
    }

    public function delete($uri)
    {
        return $this->send('DELETE', $uri);
    }


    private function send($method, $uri, $bodyData = null, $query = [])
    {
        $options = [
            RequestOptions::HEADERS => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => $this->getAuthorizationHeaderValue(),
                'User-Agent'    => FmClient::USER_AGENT,
            ],
            RequestOptions::QUERY   => $query,
        ];

        if ($bodyData) {
            $options[RequestOptions::JSON] = $bodyData;
        }

        return $this->guzzleClient->request($method, $uri, $options);
    }

    private function getBaseUri(): string
    {
        $port     = config(sprintf('filemaker.%s.port', $this->configHost));
        $protocol = config(sprintf('filemaker.%s.protocol', $this->configHost));
        $host     = config(sprintf('filemaker.%s.hostname', $this->configHost));

        if (! $protocol ||
            ! $host
        ) {
            return '';
        }

        return sprintf('%s%s%s/',
            $protocol,
            $host,
            $port ? ":{$port}" : '',
        );
    }

    private function getAuthorizationHeaderValue()
    {
        $cacheKey = sprintf('filemaker.%s.session_token', $this->configHost);
        if (cache()->has($cacheKey)) {
            return 'Bearer ' . cache($cacheKey);
        }

        $usernameAndPassword = config(sprintf('filemaker.%s.username', $this->configHost)) . ':' .
                               config(sprintf('filemaker.%s.password', $this->configHost));

        return 'Basic ' . base64_encode($usernameAndPassword);
    }

    /**
     * @return string
     */
    public function getSessionToken(): string
    {
        return $this->session_token;
    }

    /**
     * @param string $session_token
     */
    public function setSessionToken(string $session_token): void
    {
        $this->session_token = $session_token;
    }
}