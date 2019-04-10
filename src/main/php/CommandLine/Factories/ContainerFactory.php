<?php

namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;
use DI\ContainerBuilder;
use Exception;

class ContainerFactory
{
    /** @var Container */
    private static $container;

    /**
     * ContainerFactory singleton constructor.
     */
    private function __construct()
    {
    }

    /**
     * Returns the single application container instance to use.
     *
     * @return Container
     *
     * @throws Exception
     */
    public static function getContainerInstance() : Container
    {
        if (self::$container === null) {
            self::$container = self::getUnboundContainerInstance();
        }

        return self::$container;
    }

    /**
     * Returns an unbound Container which is configured like the application container. This is meant to be used for
     * functional tests only.
     *
     * @return Container
     * @throws Exception
     */
    public static function getUnboundContainerInstance() : Container
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->useAutowiring(true);
        $builder->addDefinitions(__DIR__ . '/../../../../config/phpdi/phpdi.php');
        return $builder->build();
    }
}
