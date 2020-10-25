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
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class JSStyleLintCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var JSStyleLintCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory(JSStyleLintCommand::class);
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
        $localSubject = Mockery::mock(JSStyleLintCommand::class, $this->subjectParameters)->makePartial();
        $localSubject->shouldReceive('setName')->once()->with('sca:stylelint');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run StyleLint on Less files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with(
                'This tool executes STYLELINT on a certain set of Less files of this project.'
                . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.'
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
        /** @phpstan-ignore-next-line */
        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executeFullBuildWithFix()
    {
        $mockedTargetBranch = '';
        $mockedProcessIsolation = true;
        $mockedFixMode = true;
        $expectedExitCode = 0;

        $this->prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation, $mockedFixMode);

        $this->subjectParameters[JSStyleLintAdapter::class]->shouldReceive('fixViolations')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);
        $this->subjectParameters[JSStyleLintAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);

        self::assertSame($expectedExitCode, $result);
    }

    /**
     * @test
     */
    public function executeFullBuildWithoutFix()
    {
        $mockedTargetBranch = '';
        $mockedProcessIsolation = true;
        $mockedFixMode = false;
        $expectedExitCode = 0;

        $this->prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation, $mockedFixMode);

        $this->subjectParameters[JSStyleLintAdapter::class]->shouldReceive('fixViolations')->never();
        $this->subjectParameters[JSStyleLintAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);

        self::assertSame($expectedExitCode, $result);
    }

    /**
     * This method prepares the InputInterface mocks.
     *
     * @param string $mockedTargetBranch
     * @param bool   $mockedProcessIsolation
     * @param bool   $mockedFixMode
     */
    private function prepareInputInterfaceMock(string $mockedTargetBranch, bool $mockedProcessIsolation, bool $mockedFixMode)
    {
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(false);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('process-isolation')->andReturn($mockedProcessIsolation);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('fix')->andReturn($mockedFixMode);
    }
}
