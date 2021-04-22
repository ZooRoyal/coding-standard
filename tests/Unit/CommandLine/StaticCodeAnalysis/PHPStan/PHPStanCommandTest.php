<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPStan;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\PHPStanCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TargetableToolsCommandTest;

class PHPStanCommandTest extends TargetableToolsCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'PHPStan';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontStanPHP';
        $this->allowedFileEndings = ['.php'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new PHPStanCommand($this->mockedTargetableInputFacet);
        $this->subject->injectDependenciesToolCommand(
            $this->mockedTerminalCommandRunner,
            $this->mockedEventDispatcher
        );
        $this->subject->injectDependenciesCommand($this->mockedContainer);
    }

    /**
     * @test
     */
    public function configureSetsCorrectAttributes(): void
    {
        self::assertSame('sca:stan', $this->subject->getName());
        self::assertSame('Run PHPStan on PHP files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes PHPStan on a certain set of PHP files of this project.'
            . 'It ignores files which are in directories with a .dontStanPHP file. Subdirectories are ignored too.',
            $this->subject->getHelp()
        );
    }
}
