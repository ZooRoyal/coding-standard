<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\Matcher\Closure;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\AllToolsCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

/**
 * Class AllToolsCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AllToolsCommandTest extends TestCase
{
    private AllToolsCommand $subject;
    /** @var MockInterface|FixableInputFacet */
    private FixableInputFacet $mockedFixableInputFacet;
    /** @var MockInterface|TargetableInputFacet */
    private TargetableInputFacet $mockedTargetableInputFacet;
    private InputOption $forgedFixableOption;
    private InputOption $forgedTargetableOption;
    /** @var MockInterface|InputInterface */
    private InputInterface $mockedInput;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    /** @var MockInterface|Application */
    private Application $mockedApplication;
    /** @var MockInterface|Command */
    private $mockedCommand;

    protected function setUp(): void
    {
        $this->mockedInput = Mockery::mock(InputInterface::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);
        $this->mockedApplication = Mockery::mock(Application::class);
        $this->mockedCommand = Mockery::mock(Command::class);
        $this->mockedFixableInputFacet = Mockery::mock(FixableInputFacet::class);
        $this->mockedTargetableInputFacet = Mockery::mock(TargetableInputFacet::class);
        $this->forgedFixableOption = new InputOption('fixable', null, InputOption::VALUE_REQUIRED, '', false);
        $this->forgedTargetableOption = new InputOption('targetable');

        $this->mockedTargetableInputFacet->shouldReceive('getInputDefinition->getOptions')
            ->andReturn(['asd' => $this->forgedTargetableOption]);
        $this->mockedFixableInputFacet->shouldReceive('getInputDefinition->getOptions')
            ->andReturn(['qwe' => $this->forgedFixableOption]);
        $this->mockedApplication->shouldReceive('getHelperSet')->andReturn(Mockery::mock(HelperSet::class));

        $this->subject = new AllToolsCommand($this->mockedFixableInputFacet, $this->mockedTargetableInputFacet);
        $this->subject->setApplication($this->mockedApplication);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function descriptionIsSetCorretly(): void
    {
        self::assertSame('Run all static code analysis tools.', $this->subject->getDescription());
    }

    /**
     * @test
     * @dataProvider executeCallsAllCommandsDataProvider
     *
     * @param array<string,string> $commands
     * @param array<string,string|bool> $optionsAndValues
     * @param array<int,int> $exitCodes
     */
    public function executeCallsAllCommands(
        array $commands,
        array $optionsAndValues,
        array $exitCodes,
        int $finalResult
    ): void {
        $forgedOptionsCommand = ['wub' => 'egal', 'fixable' => 'superegal'];

        $this->mockedApplication->shouldReceive('all')->with('sca')
            ->andReturn([$this->mockedCommand, $this->mockedCommand]);

        $this->mockedCommand->shouldReceive('getName')->andReturns(...$commands);
        $this->mockedCommand->shouldReceive('getDefinition->getOptions')->andReturn($forgedOptionsCommand);
        $this->mockedCommand->shouldReceive('run')
            ->with($this->assertCorrectParameterOptions($optionsAndValues), $this->mockedOutput)
            ->andReturns(...$exitCodes);

        $this->mockedInput->shouldReceive('getOptions')->once()
            ->andReturn($optionsAndValues);
        $this->mockedInput->shouldReceive('getOption')
            ->andReturnUsing(
                static function ($parameter) use ($optionsAndValues) {
                    return $optionsAndValues[$parameter];
                }
            );

        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with('<comment>All SCA-Commands will be executed.</comment>', OutputInterface::OUTPUT_NORMAL);
        $this->mockedOutput->shouldReceive('writeln')->times(count(array_filter($exitCodes)))
            ->with(
                H::allOf(H::startsWith('<error>Exitcode:'), H::endsWith('</error>')),
                OutputInterface::OUTPUT_NORMAL
            );

        $result = $this->subject->execute($this->mockedInput, $this->mockedOutput);

        self::assertSame($finalResult, $result);
    }

    /** @return array<string,array<string,array<int|string,bool|int|string>|int>> */
    public function executeCallsAllCommandsDataProvider(): array
    {
        return [
            'one success' => [
                'commands' => ['sca:argh!', 'sca:all'],
                'optionsAndValues' => ['wub' => 'rums', 'fixable' => true, 'targetable' => false],
                'exitCodes' => [0],
                'finalResult' => 0,
            ],
            'one failure' => [
                'commands' => ['sca:argh!', 'sca:schwurbel'],
                'optionsAndValues' => ['wub' => 'rums', 'fixable' => true, 'targetable' => false],
                'exitCodes' => [0, 1],
                'finalResult' => 1,
            ],
            'multiple failures' => [
                'commands' => ['sca:argh!', 'sca:schwurbel'],
                'optionsAndValues' => ['wub' => 'rums', 'fixable' => true, 'targetable' => false],
                'exitCodes' => [2, 1],
                'finalResult' => 2,
            ],
        ];
    }

    /**
     * @test
     */
    public function helpIsSetCorretly(): void
    {
        self::assertSame(
            'This tool executes all static code analysis tools on files of this project. '
            . 'It ignores files which are in directories with a .dont<toolshortcut> file. Subdirectories are ignored too.',
            $this->subject->getHelp()
        );
    }

    /**
     * @test
     */
    public function nameIsSetCorretly(): void
    {
        self::assertSame('sca:all', $this->subject->getName());
    }

    /**
     * @test
     */
    public function optionsIsSetCorretly(): void
    {
        $inputOptions = $this->subject->getDefinition()->getOptions();

        MatcherAssert::assertThat(
            $inputOptions,
            H::allOf(H::hasItem($this->forgedFixableOption), H::hasItem($this->forgedFixableOption))
        );
    }

    /**
     * Creates a closure to make sure that all parameters are set
     *
     * @param array<string,string> $optionsAndValues
     */
    private function assertCorrectParameterOptions(array $optionsAndValues): Closure
    {
        return Mockery::on(
            static function (ArrayInput $sut) use ($optionsAndValues): bool {
                foreach ($optionsAndValues as $option => $value) {
                    MatcherAssert::assertThat($sut->getParameterOption(['--' . $option], H::equalTo($value)));
                }
                return true;
            }
        );
    }
}
