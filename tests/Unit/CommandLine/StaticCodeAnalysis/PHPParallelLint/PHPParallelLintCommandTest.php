<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPParallelLint;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint\PHPParallelLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPParallelLint\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TargetableToolsCommandTest;

class PHPParallelLintCommandTest extends TargetableToolsCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'PHP Parallel Lint';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontLintPHP';
        $this->allowedFileEndings = ['php'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new PHPParallelLintCommand($this->mockedTargetableInputFacet);
        $this->subject->injectDependenciesToolCommand(
            $this->mockedTerminalCommandRunner,
            $this->mockedEventDispatcher,
        );
        $this->subject->injectDependenciesCommand($this->mockedContainer);
    }

    /**
     * @test
     */
    public function configureSetsCorrectAttributes(): void
    {
        self::assertSame('sca:parallel-lint', $this->subject->getName());
        self::assertSame('Run Parallel-Lint on PHP files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes Parallel-Lint on a certain set of PHP files of this project. It '
            . 'ignores files which are in directories with a .dontLintPHP file. Subdirectories are ignored too.',
            $this->subject->getHelp(),
        );
    }
}
