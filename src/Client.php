<?php


namespace Flooris\FileMakerDataApi;


use Flooris\FileMakerDataApi\Api\Authentication;
use Flooris\FileMakerDataApi\Api\MetaData;
use Flooris\FileMakerDataApi\Api\Record;
use Flooris\FileMakerDataApi\Api\Script;
use Flooris\FileMakerDataApi\HttpClient\Connector;
use Illuminate\Support\Str;

class Client
{
    public const USER_AGENT = 'flooris-filemaker-data-api';

    public $configHost;
    public $connector;

    public function __construct($configHost = "default", Connector $connector = null)
    {
        $this->configHost = $configHost;
        $this->connector  = $connector;

        if ($this->connector === null) {
            $this->connector = new Connector($configHost);
        }

        $this->validateSession();
    }

    public function record($layout = null)
    {
        return new Record($this, $layout);
    }

    public function authentication()
    {
        return new Authentication($this);
    }

    public function metaData()
    {
        return new MetaData($this);
    }

    public function script($layout = null)
    {
        return new Script($this, $layout);
    }

    public function validateSession()
    {
        $cacheKey = sprintf('filemaker.%s.session_token', $this->configHost);

        if (! cache()->has($cacheKey) || Str::length(cache($cacheKey)) < 20) {
            $this->authentication()->login();
        }
    }
}