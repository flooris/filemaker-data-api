<?php


namespace Flooris\FileMakerDataApi\Api;


use Psr\SimpleCache\InvalidArgumentException;

class MetaData extends ApiAbstract
{
    /**
     * @throws InvalidArgumentException
     */
    public function getProductInfo(): object
    {
        $this->includeLayout   = false;
        $this->includeDatabase = false;

        return $this->get('productInfo');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getDatabaseNames(): object
    {
        $this->includeLayout   = false;
        $this->includeDatabase = false;

        return $this->get('databases');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getScriptNames(): object
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('scripts');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getLayoutNames(): object
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('layouts');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getLayoutMetaData($layoutName): object
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('layouts/%s', [$layoutName]);
    }
}