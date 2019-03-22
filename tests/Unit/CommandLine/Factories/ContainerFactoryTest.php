<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;

class ContainerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function getContainerInstance()
    {
        $result    = ContainerFactory::getUnboundContainerInstance();
        $buildItem = $result->get(OutputInterface::class);

        self::assertInstanceOf(Container::class, $result);
        self::assertInstanceOf(OutputInterface::class, $buildItem);
    }

    /**
     * @test
     */
    public function containerFactoryIsCantBeInstantiated()
    {
        $reflection = new ReflectionClass(ContainerFactory::class);
        self::assertFalse($reflection->isInstantiable());
    }

    /**
     * @test
     */
    public function getContainerInstanceReturnsSameInstance()
    {
        $result1    = ContainerFactory::getContainerInstance();
        $result2    = ContainerFactory::getContainerInstance();

        self::assertSame($result1, $result2);
    }
}
