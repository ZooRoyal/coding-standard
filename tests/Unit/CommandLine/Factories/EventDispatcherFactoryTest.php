<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\GitCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\EventSubscriber\TerminalCommandPreconditionChecker;
use Zooroyal\CodingStandard\CommandLine\Factories\EventDispatcherFactory;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class EventDispatcherFactoryTest extends TestCase
{
    private EventDispatcherFactory $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;
    /** @var array<string> */
    private array $subscribers = [GitCommandPreconditionChecker::class, TerminalCommandPreconditionChecker::class];

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(EventDispatcherFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function build(): void
    {
        $mockedEventDispatcher = Mockery::mock('overload:' . EventDispatcher::class);

        foreach ($this->subscribers as $subscriber) {
            $mockedSubscriber = Mockery::mock($subscriber);
            $this->subjectParameters[Container::class]->shouldReceive('get')->once()
                ->with($subscriber)->andReturn($mockedSubscriber);
            $mockedEventDispatcher->shouldReceive('addSubscriber')->once()
                ->with($mockedSubscriber);
        }

        $result = $this->subject->build();

        /** @phpstan-ignore-next-line */
        self::assertSame($result->mockery_getName(), $mockedEventDispatcher->mockery_getName());
    }
}
