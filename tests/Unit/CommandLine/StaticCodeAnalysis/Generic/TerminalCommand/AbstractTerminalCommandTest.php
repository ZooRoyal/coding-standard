<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;

class AbstractTerminalCommandTest extends TestCase
{
    /** @var MockInterface|AbstractTerminalCommand */
    private $subject;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedOutput = Mockery::mock(OutputInterface::class);
        $this->subject = Mockery::mock(AbstractTerminalCommand::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->subject->injectDependenciesAbstractTerminalCommand($this->mockedOutput);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function toStringCallsCompileCycle(): void
    {
        $this->subject->shouldReceive('compile')->once();
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                '<info>Compiled TerminalCommand to following string</info>' . PHP_EOL . PHP_EOL,
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

        $this->subject->__toString();
        $this->subject->__toString();
    }

    /**
     * @test
     */
    public function toArrayCallsCompileCycle(): void
    {
        $this->subject->shouldReceive('compile')->once();
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                '<info>Compiled TerminalCommand to following string</info>' . PHP_EOL . PHP_EOL,
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

        $this->subject->toArray();
        $this->subject->toArray();
    }

    /**
     * @test
     */
    public function compileExceptionWillBeCought(): void
    {
        $forgedException = new Exception();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong compiling a command');
        $this->expectExceptionCode(1616426291);

        $this->subject->shouldReceive('compile')->once()->andThrow($forgedException);
        $this->mockedOutput->shouldReceive('writeln')->never()
            ->with(
                '<info>Compiled TerminalCommand to following string</info>' . PHP_EOL . PHP_EOL,
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

        $this->subject->toArray();
    }
}
