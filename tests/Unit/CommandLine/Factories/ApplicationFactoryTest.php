<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use DI\Container;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\Checks\ForbiddenChangesCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\AllToolsCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\JSESLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPCodeSnifferCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPCopyPasteDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPMessDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPParallelLintCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPStanCommand;
use Zooroyal\CodingStandard\CommandLine\Factories\ApplicationFactory;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class ApplicationFactoryTest extends TestCase
{
    private ApplicationFactory $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;
    /** @var array<string> */
    private array $commands = [
        PHPParallelLintCommand::class,
        PHPCodeSnifferCommand::class,
        PHPStanCommand::class,
        FindFilesToCheckCommand::class,
        PHPMessDetectorCommand::class,
        PHPCopyPasteDetectorCommand::class,
        JSESLintCommand::class,
        JSStyleLintCommand::class,
        AllToolsCommand::class,
        ForbiddenChangesCommand::class,
    ];

    public function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(ApplicationFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function build(): void
    {
        $mockedApplication = Mockery::mock('overload:' . Application::class);
        $mockedCommand = Mockery::mock(Command::class);

        $mockedApplication->shouldReceive('setDispatcher')->once()
            ->with($this->subjectParameters[EventDispatcherInterface::class]);

        $this->subjectParameters[Container::class]->shouldReceive('get')
            ->with(Matchers::anyOf(...$this->commands))->andReturn($mockedCommand);
        $mockedApplication->shouldReceive('add')->times(count($this->commands))
            ->with($mockedCommand);

        $result = $this->subject->build();

        /** @phpstan-ignore-next-line */
        self::assertSame($result->mockery_getName(), $mockedApplication->mockery_getName());
    }
}
