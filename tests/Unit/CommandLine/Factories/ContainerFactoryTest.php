<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use DI\ContainerBuilder;
use Hamcrest\Matchers;
use Mockery;
use Amp\PHPUnit\AsyncTestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;

class ContainerFactoryTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function getContainerInstance()
    {
        $result = ContainerFactory::getUnboundContainerInstance();
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
        $result1 = ContainerFactory::getContainerInstance();
        $result2 = ContainerFactory::getContainerInstance();

        self::assertSame($result1, $result2);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function getContainerInstanceConfiguresContainer()
    {
        $expectedContainer = Mockery::mock(Container::class);
        $mockedContainerBuilder = Mockery::mock('overload:'. ContainerBuilder::class);

        $mockedContainerBuilder->shouldReceive('useAnnotations')->once()->with(true);
        $mockedContainerBuilder->shouldReceive('useAutowiring')->once()->with(true);
        $mockedContainerBuilder->shouldReceive('addDefinitions')->once()
            ->with(Matchers::endsWith('/../Config/phpdi.php'));
        $mockedContainerBuilder->shouldReceive('build')->once()
            ->withNoArgs()->andReturn($expectedContainer);

        $result = ContainerFactory::getUnboundContainerInstance();

        self::assertSame($expectedContainer, $result);
    }
}
