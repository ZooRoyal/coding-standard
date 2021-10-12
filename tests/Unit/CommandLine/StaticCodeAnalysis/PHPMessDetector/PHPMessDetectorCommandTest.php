<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPMessDetector;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector\PHPMessDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TargetableToolsCommandTest;

class PHPMessDetectorCommandTest extends TargetableToolsCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'PHP Mess Detector';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontMessDetectPHP';
        $this->allowedFileEndings = ['php'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new PHPMessDetectorCommand($this->mockedTargetableInputFacet);
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
        self::assertSame('sca:mess-detect', $this->subject->getName());
        self::assertSame('Run PHP-MD on PHP files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes PHP-MD on a certain set of PHP files of this project. It ignores files which are in '
            . 'directories with a .dontMessDetectPHP file. Subdirectories are ignored too.',
            $this->subject->getHelp()
        );
    }
}
