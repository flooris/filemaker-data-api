<?php


namespace Flooris\FileMakerDataApi\Api;


class MetaData extends ApiAbstract
{
    public function getProductInfo()
    {
        $this->includeLayout   = false;
        $this->includeDatabase = false;

        return $this->get('productInfo');
    }

    public function getDatabaseNames()
    {
        $this->includeLayout   = false;
        $this->includeDatabase = false;

        return $this->get('databases');
    }

    public function getScriptNames()
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('scripts');
    }

    public function getLayoutNames()
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('layouts');
    }

    public function getLayoutMetaData($layoutName)
    {
        $this->includeDatabase = true;
        $this->includeLayout   = false;

        return $this->get('layouts/%s', [$layoutName]);
    }
}