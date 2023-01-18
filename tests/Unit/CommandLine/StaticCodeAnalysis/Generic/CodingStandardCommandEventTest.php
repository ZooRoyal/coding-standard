<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\CodingStandardCommandEvent;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TerminalCommand;

class CodingStandardCommandEventTest extends TestCase
{
    private CodingStandardCommandEvent $subject;
    private MockInterface|Command $mockedCommand;
    private MockInterface|InputInterface $mockedInputInterface;
    private MockInterface|OutputInterface $mockedOutputInterface;
    private string $forgedExclusionListToken;
    /** @var array<string> */
    private array $forgedallowedFileEndings;
    private MockInterface|TerminalCommand $mockedterminalCommand;

    protected function setUp(): void
    {
        $this->mockedCommand = Mockery::mock(Command::class);
        $this->mockedInputInterface = Mockery::mock(InputInterface::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedterminalCommand = Mockery::mock(TerminalCommand::class);
        $this->forgedExclusionListToken = 'asdqwe';
        $this->forgedallowedFileEndings = ['aaa', 'üüüüü'];
        $this->subject = new CodingStandardCommandEvent(
            $this->mockedCommand,
            $this->mockedInputInterface,
            $this->mockedOutputInterface,
            $this->mockedterminalCommand,
            $this->forgedExclusionListToken,
            $this->forgedallowedFileEndings,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function isConsoleEvent(): void
    {
        self::assertInstanceOf(ConsoleEvent::class, $this->subject);
    }

    /**
     * @test
     */
    public function setterGetterLifecycle(): void
    {
        $command = $this->subject->getCommand();
        $input = $this->subject->getInput();
        $output = $this->subject->getOutput();
        $terminalCommandName = $this->subject->getTerminalCommand();
        $exclusionListToken = $this->subject->getExclusionListToken();
        $allowedFileEndings = $this->subject->getAllowedFileEndings();

        self::assertSame($this->mockedCommand, $command);
        self::assertSame($this->mockedInputInterface, $input);
        self::assertSame($this->mockedOutputInterface, $output);
        self::assertSame($this->mockedterminalCommand, $terminalCommandName);
        self::assertSame($this->forgedExclusionListToken, $exclusionListToken);
        self::assertSame($this->forgedallowedFileEndings, $allowedFileEndings);
    }
}
