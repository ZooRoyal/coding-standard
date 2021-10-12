<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector\PHPCopyPasteDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\AbstractToolCommandTest;

class PHPCopyPasteDetectorCommandTest extends AbstractToolCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'PHP Copy Paste Detector';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontCopyPasteDetectPHP';
        $this->allowedFileEndings = ['.php'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new PHPCopyPasteDetectorCommand();
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
        self::assertSame('sca:copy-paste-detect', $this->subject->getName());
        self::assertSame('Run PHP-CPD on PHP files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes PHP-CPD on a certain set of PHP files of this project. It ignores '
            . 'files which are in directories with a .dontCopyPasteDetectPHP file. Subdirectories are ignored too.',
            $this->subject->getHelp()
        );
    }
}
