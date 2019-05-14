<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\AllToolsCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;

class AllToolsCommandTest extends TestCase
{
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $this->mockedInputInterface = Mockery::mock(InputInterface::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure()
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(AllToolsCommand::class)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sca:all');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run all static code analysis tools.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with(
                'This tool executes all static code analysis tools on files of this project. '
                . 'It ignores files which are in directories with a .dont<toolshortcut> file. Subdirectories are ignored too.'
            );
        $localSubject->shouldReceive('setDefinition')->once()
            ->with(
                Mockery::on(
                    function ($value) {
                        MatcherAssert::assertThat($value, H::anInstanceOf(InputDefinition::class));
                        /** @var InputDefinition $value */
                        $options = $value->getOptions();
                        MatcherAssert::assertThat(
                            $options,
                            H::allOf(
                                H::arrayWithSize(4),
                                H::everyItem(
                                    H::anInstanceOf(InputOption::class)
                                )
                            )
                        );

                        return true;
                    }
                )
            );

        $localSubject->configure();
    }

    public function executeRunsAllCommandsDataProvider()
    {
        return [
            'success' => ['returnValue' => 0, 'outputCount' => 0],
            'failure' => ['returnValue' => 1, 'outputCount' => 2],
        ];
    }

    /**
     * @test
     * @dataProvider executeRunsAllCommandsDataProvider
     */
    public function executeRunsAllCommands($returnValue, $outputCount)
    {
        /** @var MockInterface|AllToolsCommand $subject */
        $subject = Mockery::mock(AllToolsCommand::class)->makePartial();
        $mockedCommand = Mockery::mock(Command::class);
        $mockedCommandAll = Mockery::mock(Command::class);
        $sharedOption = 'option1';
        $sharedOptionValue = 'blablabla';
        $forgedInputOptions = [$sharedOption => 'bla', 'asd' => 'qwe'];
        $forgedCommandOptions = [$sharedOption => 'blub', 'qwe' => 'asd'];

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('All SCA-Commands will be executed.', 1);
        $this->mockedInputInterface->shouldReceive('getOptions')->once()
            ->andReturn($forgedInputOptions);

        $subject->shouldReceive('getApplication->all')->once()
            ->with('sca')->andReturn([$mockedCommandAll, $mockedCommand, $mockedCommand]);

        $mockedCommandAll->shouldReceive('getName')->once()
            ->andReturn('sca:all');
        $mockedCommand->shouldReceive('getName')
            ->andReturn('sca:bla');
        $mockedCommand->shouldReceive('getDefinition->getOptions')
            ->andReturn($forgedCommandOptions);

        $this->mockedInputInterface->shouldReceive('getOption')
            ->with($sharedOption)->andReturn($sharedOptionValue);

        $mockedCommand->shouldReceive('run')
            ->with(H::anInstanceOf(ArrayInput::class), $this->mockedOutputInterface)
            ->andReturn($returnValue);

        $this->mockedOutputInterface->shouldReceive('writeln')->times($outputCount)
            ->with('Exitcode:' . $returnValue, 1);
        $result = $subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
        self::assertSame($returnValue, $result);
    }
}
