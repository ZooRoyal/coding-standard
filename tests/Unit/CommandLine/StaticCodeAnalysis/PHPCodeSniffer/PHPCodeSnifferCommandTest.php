<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPCodeSniffer;

use DI\Container;
use Mockery;
use Mockery\MockInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer\PHPCodeSnifferCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\FixingToolCommandTest;

class PHPCodeSnifferCommandTest extends FixingToolCommandTest
{
    /** @var Container|MockInterface */
    private Container $mockedContainer;

    protected function setUp(): void
    {
        $this->terminalCommandName = 'PHP Code Sniffer';
        $this->terminalCommandType = TerminalCommand::class;
        $this->exclusionToken = '.dontSniffPHP';
        $this->allowedFileEndings = ['php'];
        $this->mockedTerminalCommand = Mockery::mock(TerminalCommand::class);
        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedContainer->shouldReceive('make')->atLeast()->once()
            ->with(TerminalCommand::class)
            ->andReturn($this->mockedTerminalCommand);

        parent::setUp();

        $this->subject = new PHPCodeSnifferCommand($this->mockedFixableInputFacet, $this->mockedTargetableInputFacet);
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
        self::assertSame('sca:sniff', $this->subject->getName());
        self::assertSame('Run PHP-CS on PHP files.', $this->subject->getDescription());
        self::assertSame(
            'This tool executes PHP-CS on a certain set of PHP files of this project. '
            . 'It ignores files which are in directories with a .dontSniffPHP file. Subdirectories are ignored too.',
            $this->subject->getHelp()
        );
    }
}
