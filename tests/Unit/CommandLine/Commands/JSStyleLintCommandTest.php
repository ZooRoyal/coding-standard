<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\JSStyleLintCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class JSStyleLintCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var FindFilesToCheckCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(JSStyleLintCommand::class);
        $this->subject           = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->mockedInputInterface  = Mockery::mock(InputInterface::class);
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
        $localSubject = Mockery::mock(JSStyleLintCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sca:stylelint');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run StyleLint on Less files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool executes STYLELINT on a certain set of Less files of this Project.'
                . 'Add a .dontSniffLESS file to <LESS-DIRECTORIES> that should be ignored.');
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

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executeFullBuildWithFix()
    {
        $mockedTargetBranch     = '';
        $mockedProcessIsolation = true;
        $mockedFixMode          = true;
        $expectedExitCode       = 0;

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
        $mockedTargetBranch     = '';
        $mockedProcessIsolation = true;
        $mockedFixMode          = false;
        $expectedExitCode       = 0;

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
     * @param $mockedTargetBranch
     * @param $mockedProcessIsolation
     */
    private function prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation, $mockedFixMode)
    {
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('process-isolation')->andReturn($mockedProcessIsolation);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('fix')->andReturn($mockedFixMode);
    }
}
