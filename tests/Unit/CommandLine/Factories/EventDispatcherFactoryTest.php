<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\CommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\Factories\EventDispatcherFactory;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class EventDispatcherFactoryTest extends TestCase
{
    private EventDispatcherFactory $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(EventDispatcherFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function build()
    {
        $mockedEventDispatcher = Mockery::mock('overload:' . EventDispatcher::class);
        $mockedCommandPreconditionChecker = Mockery::mock(CommandPreconditionChecker::class);

        $this->subjectParameters[Container::class]->shouldReceive('get')
            ->with(CommandPreconditionChecker::class)->andReturn($mockedCommandPreconditionChecker);
        $mockedEventDispatcher->shouldReceive('addSubscriber')->once()
            ->with($mockedCommandPreconditionChecker);

        $result = $this->subject->build();

        /** @phpstan-ignore-next-line */
        self::assertSame($result->mockery_getName(), $mockedEventDispatcher->mockery_getName());
    }
}
