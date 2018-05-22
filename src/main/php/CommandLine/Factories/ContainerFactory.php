<?php
namespace Zooroyal\CodingStandard\CommandLine\Factories;

use DI\Container;

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
     * Returns the single container instance to use.
     *
     * @return Container
     */
    public static function getContainerInstance()
    {
        if (self::$container === null) {
            $builder = new \DI\ContainerBuilder();
            $builder->useAnnotations(true);
            $builder->useAutowiring(true);
            $builder->addDefinitions(__DIR__ . '/../../../../config/phpdi/phpdi.php');
            self::$container = $builder->build();
        }

        return self::$container;
    }
}
