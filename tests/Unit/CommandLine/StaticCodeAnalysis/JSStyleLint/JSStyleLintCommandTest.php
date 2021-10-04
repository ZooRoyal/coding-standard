<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\JSStyleLint;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommandTest;

class JSStyleLintCommandTest extends FixingToolCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;
    /** @var MockInterface|TerminalCommandFinder */
    private TerminalCommandFinder $mockedTerminalCommandFinder;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'StyleLint';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontSniffLESS';
        $this->allowedFileEndings = ['css', 'scss', 'sass', 'less'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new JSStyleLintCommand($this->mockedFixableInputFacet, $this->mockedTargetableInputFacet);
        $this->subject->injectDependenciesToolCommand(
            $this->mockedTerminalCommandRunner,
            $this->mockedEventDispatcher
        );
        $this->subject->injectDependenciesCommand($this->mockedContainer, $this->mockedTerminalCommandFinder);
    }

    /**
     * @test
     */
    public function executeRunsTerminalCommand(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->with('stylelint');
        parent::executeRunsTerminalCommand();
    }

    /**
     * @test
     */
    public function executeWrappsException(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->with('stylelint');
        parent::executeRunsTerminalCommand();
    }

    /**
     * @test
     */
    public function configureSetsCorrectAttributes(): void
    {
        self::assertSame('sca:stylelint', $this->subject->getName());
        self::assertSame('Run StyleLint on Less files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes STYLELINT on a certain set of Less files of this project.'
            . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.',
            $this->subject->getHelp()
        );
    }

    /**
     * @test
     */
    public function executeSkipsCommandIfNotFound(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->andThrow(new TerminalCommandNotFoundException());

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<info>Stylelint could not be found. To use this sniff please refer to the README.md</info>');

        $result = $this->subject->execute($this->mockedInput, $this->mockedOutput);

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function executeWarnsAboutNoUsefulFilesToSniff(): void
    {
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')->once()
            ->with('stylelint');
        parent::executeWarnsAboutNoUsefulFilesToSniff();
    }
}
