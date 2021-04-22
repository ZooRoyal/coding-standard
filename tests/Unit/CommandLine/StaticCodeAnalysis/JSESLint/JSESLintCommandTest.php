<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\JSESLint;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSESLint\JSESLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSESLint\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommandTest;

class JSESLintCommandTest extends FixingToolCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;
    /** @var MockInterface|TerminalCommandFinder */
    private TerminalCommandFinder $mockedTerminalCommandFinder;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'EsLint';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontSniffJS';
        $this->allowedFileEndings = ['js', 'ts', 'jsx', 'tsx'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new JSESLintCommand($this->mockedFixableInputFacet, $this->mockedTargetableInputFacet);
        $this->subject->injectDependenciesToolCommand(
            $this->mockedTerminalCommandRunner,
            $this->mockedEventDispatcher
        );
        $this->subject->injectDependenciesCommand($this->mockedContainer, $this->mockedTerminalCommandFinder);
    }

    /**
     * @test
     */
    public function configureSetsCorrectAttributes()
    {
        self::assertSame('sca:eslint', $this->subject->getName());
        self::assertSame('Run ESLint on JS files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes ESLINT on a certain set of JS files of this project.'
            . ' Add a .dontSniffJS file to <JS-DIRECTORIES> that should be ignored.',
            $this->subject->getHelp()
        );
    }

    /**
     * @test
     */
    public function executeRunsTerminalCommand(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->with('eslint');
        parent::executeRunsTerminalCommand();
    }

    /**
     * @test
     */
    public function executeSkipsCommandIfNotFound(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->andThrow(new TerminalCommandNotFoundException());

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>EsLint could not be found. To use this sniff please refer to the README.md</info>');

        $result = $this->subject->execute($this->mockedInput, $this->mockedOutput);

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function executeWrappsException()
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->with('eslint');
        parent::executeRunsTerminalCommand();
    }
}
