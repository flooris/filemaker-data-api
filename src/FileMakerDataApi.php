<?php

namespace Flooris\FileMakerDataApi;

use Exception;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;
use Flooris\FileMakerDataApi\Api\Record;
use Flooris\FileMakerDataApi\Api\Script;
use GuzzleHttp\Exception\GuzzleException;
use Flooris\FileMakerDataApi\Api\MetaData;
use Psr\SimpleCache\InvalidArgumentException;
use Flooris\FileMakerDataApi\Api\Authentication;
use Flooris\FileMakerDataApi\HttpClient\Connector;
use Flooris\FileMakerDataApi\RecordRepository\FmObject;
use Flooris\FileMakerDataApi\RecordRepository\FmBaseRepository;
use Flooris\FileMakerDataApi\RecordRepository\FmObjectRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepositoryInterface;

class FileMakerDataApi
{
    public const USER_AGENT = 'flooris-filemaker-data-api';

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected CacheRepositoryInterface $cache,
        public string $configHost = 'default',
        public ?Connector $connector = null,
        array $guzzleConfig = []
    ) {
        if ($this->connector === null) {
            $this->connector = new Connector($configHost, $cache, $guzzleConfig);
        }

        if ($this->connector->hasValidConnectionCredentials()) {
            $this->validateSession();
        }
    }

    public function repo(FmObject $fmObject)
    {
        return new FmObjectRepository($this, $fmObject);
    }

    public function record($layout = null): Record
    {
        return new Record($this, $layout);
    }

    public function authentication(): Authentication
    {
        return new Authentication($this);
    }

    public function metaData(): MetaData
    {
        return new MetaData($this);
    }

    public function script($layout = null): Script
    {
        return new Script($this, $layout);
    }

    public function getDataContainerToken(string $dataContainerObjectUrl): ?string
    {
        return $this->connector->getDataContainerToken($dataContainerObjectUrl);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSessionToken(bool $validateSession = true): ?string
    {
        if ($validateSession) {
            $this->validateSession();
        }

        return $this->getSessionTokenFromCache();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function validateSession(): void
    {
        $sessionToken = $this->getSessionTokenFromCache();

        if (! $sessionToken || Str::length($sessionToken) < 20) {
            $this->authentication()->login();
        }
    }

    private function sessionTTL(): int
    {
        return (int) config('filemaker.settings.session_ttl');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSessionTokenFromCache(): ?string
    {
        return $this->cache->get($this->getSessionTokenCacheKey());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteSessionToken(): void
    {
        $this->cache->delete($this->getSessionTokenCacheKey());
    }

    public function getSessionTokenCacheKey(): string
    {
        return sprintf('filemaker.%s.session_token', $this->configHost);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setOrExtendSessionToken(?string $sessionToken = null): void
    {
        if (! $sessionToken) {
            $sessionToken = $this->getSessionTokenFromCache();
        }

        if (! $sessionToken) {
            return;
        }

        $cacheKey = $this->getSessionTokenCacheKey();

        $this->cache->set($cacheKey, $sessionToken, $this->sessionTTL());
    }

    /**
     * @throws GuzzleException
     */
    public function getDataContainerContent(string $dataContainerObjectUrl, string $dataContainerToken): ?StreamInterface
    {
        return $this->connector->getDataContainerContent($dataContainerObjectUrl, $dataContainerToken);
    }
}
