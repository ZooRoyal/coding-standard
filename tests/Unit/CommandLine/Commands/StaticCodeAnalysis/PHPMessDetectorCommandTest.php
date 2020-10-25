<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPMessDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPMessDetectorAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PHPMessDetectorCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var PHPMessDetectorCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory(PHPMessDetectorCommand::class);
        $this->subjectParameters = $subjectFactory->buildParameters();
        $this->subject = $subjectFactory->buildSubjectInstance($this->subjectParameters);

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
        $localSubject = Mockery::mock(PHPMessDetectorCommand::class, $this->subjectParameters)->makePartial();
        $localSubject->shouldReceive('setName')->once()->with('sca:mess-detect');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run PHP-MD on PHP files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with(
                'This tool executes PHP-MD on a certain set of PHP files of this project. It ignores files ' .
                'which are in directories with a .dontMessDetectPHP file. Subdirectories are ignored too.'
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
                                H::arrayWithSize(3),
                                H::everyItem(
                                    H::anInstanceOf(InputOption::class)
                                )
                            )
                        );

                        return true;
                    }
                )
            );
        /** @phpstan-ignore-next-line */
        $localSubject->configure();
    }


    /**
     * @test
     */
    public function writeViolationsToOutput()
    {
        $mockedTargetBranch = '';
        $mockedProcessIsolation = true;
        $expectedExitCode = 0;

        $this->prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation);

        $this->subjectParameters[PHPMessDetectorAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);

        self::assertSame($expectedExitCode, $result);
    }

    /**
     * This method prepares the InputInterface mocks.
     *
     * @param string $mockedTargetBranch
     * @param bool   $mockedProcessIsolation
     */
    private function prepareInputInterfaceMock(string $mockedTargetBranch, bool $mockedProcessIsolation)
    {
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(false);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('process-isolation')->andReturn($mockedProcessIsolation);
    }
}
