<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use DI\ContainerBuilder;
use Hamcrest\Matchers;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;

class ContainerFactoryTest extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getContainerInstance(): void
    {
        $result = ContainerFactory::getUnboundContainerInstance();
        $buildItem = $result->get(OutputInterface::class);

        self::assertInstanceOf(Container::class, $result);
        self::assertInstanceOf(OutputInterface::class, $buildItem);
    }

    /**
     * @test
     */
    public function containerFactoryIsCantBeInstantiated(): void
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
