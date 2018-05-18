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
use Zooroyal\CodingStandard\CommandLine\Commands\PHPCodeSnifferCommand;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PHPCodeSnifferCommandTest extends TestCase
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
        $buildFragments          = $subjectFactory->buildSubject(PHPCodeSnifferCommand::class);
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
        $localSubject = Mockery::mock(PHPCodeSnifferCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sniff');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run PHP-CS on PHP files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool executes PHP-CS on a certain set of PHP files of this Project. '
                . 'It ignores files which are in directories with a .dontSniffPHP file. Subdirectories are ignored too.');
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

        $this->subjectParameters[PHPCodeSnifferAdapter::class]->shouldReceive('fixViolations')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);
        $this->subjectParameters[PHPCodeSnifferAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
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

        $this->subjectParameters[PHPCodeSnifferAdapter::class]->shouldReceive('fixViolations')->never();
        $this->subjectParameters[PHPCodeSnifferAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);

        self::assertSame($expectedExitCode, $result);
    }

    /**
     * This method prepares the InputInterface mocks.
     *
     * @param $mockedTargetBranch
     * @param $mockedProcessIsolation
     * @param $mockedFixMode
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
