<?php
namespace Flooris\FileMakerDataApi;

class InstanceManager
{
    private static self $instance;
    private array $clientInstances = [];
    // Private constructor to prevent instantiation from outside the class
    private function __construct()
    {
        // Initialization code, if any
    }

    public static function init(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function setInstance(string $name, Client $client): void
    {
        $instanceManager = self::init();
        $instanceManager->clientInstances[$name] = $client;
    }

    // Public method to get the instance of the class
    public static function getInstance(string $name): ?Client
    {
        return self::init()->clientInstances[$name]?->init();
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserialization of the instance
    private function __wakeup()
    {
    }
}
