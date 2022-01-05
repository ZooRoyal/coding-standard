<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic;

use Exception;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandDecorator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommandRunner;

abstract class AbstractToolCommandTest extends TestCase
{
    protected AbstractToolCommand $subject;
    /** @var MockInterface|TerminalCommandRunner */
    protected TerminalCommandRunner $mockedTerminalCommandRunner;
    /** @var MockInterface|EventDispatcherInterface */
    protected EventDispatcherInterface $mockedEventDispatcher;
    /** @var MockInterface|InputInterface */
    protected InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    protected OutputInterface $mockedOutput;
    protected string $terminalCommandName;
    protected string $terminalCommandType;
    protected string $exclusionToken;
    /** @var array<string> */
    protected array $allowedFileEndings;
    protected TerminalCommand $mockedTerminalCommand;

    protected function setUp(): void
    {
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedTerminalCommandRunner = Mockery::mock(TerminalCommandRunner::class);
        $this->mockedEventDispatcher = Mockery::mock(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function executeRunsTerminalCommand(): void
    {
        $expectedExitCode = 0;
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(PHP_EOL . '<comment>Running ' . $this->terminalCommandName . '</comment>');

        $this->mockedEventDispatcher->shouldReceive('dispatch')->once()->with(
            H::allOf(
                HasProperty::hasProperty(
                    'subject',
                    H::anInstanceOf($this->terminalCommandType)
                ),
                HasProperty::hasProperty(
                    'arguments',
                    H::allOf(
                        H::hasItem($this->exclusionToken),
                        H::hasItem($this->allowedFileEndings),
                        H::hasItem($this->mockedInput),
                        H::hasItem($this->mockedOutput),
                    )
                )
            ),
            TerminalCommandDecorator::EVENT_DECORATE_TERMINAL_COMMAND
        );
        $this->mockedTerminalCommandRunner->shouldReceive('run')->once()
            ->with($this->mockedTerminalCommand)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInput, $this->mockedOutput);

        self::assertSame($expectedExitCode, $result);
    }

    /**
     * @test
     */
    public function executeWrappsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1617786765);
        $this->expectExceptionMessage('Something went wrong while executing a terminal command.');

        $this->mockedOutput->shouldIgnoreMissing();
        $this->mockedEventDispatcher->shouldIgnoreMissing();
        $this->mockedTerminalCommandRunner->shouldReceive('run')->once()
            ->with(Mockery::any())->andThrow(new Exception());

        $this->subject->execute($this->mockedInput, $this->mockedOutput);
    }

    /**
     * @test
     */
    public function executeWarnsAboutNoUsefulFilesToSniff(): void
    {
        $localMassage = 'Hamlo ich bin 1 problem';
        $localCode = 123456;

        $this->mockedOutput->shouldIgnoreMissing();
        $this->mockedEventDispatcher->shouldIgnoreMissing();

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('Skipping tool.');
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                'Reason to skip tool: ' . $localMassage . PHP_EOL . 'Code: ' . $localCode,
                OutputInterface::VERBOSITY_VERBOSE
            );

        $this->mockedTerminalCommandRunner->shouldReceive('run')->once()
            ->with(Mockery::any())->andThrow(new NoUsefulCommandFoundException($localMassage, $localCode));

        $result = $this->subject->execute($this->mockedInput, $this->mockedOutput);

        //exitcode auf 0 prüfen
        self::assertSame(0, $result);
    }
}
