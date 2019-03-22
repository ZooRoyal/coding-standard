<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPParallelLintCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPParallelLintAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PHPParallelLintCommandTest extends TestCase
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
        $buildFragments          = $subjectFactory->buildSubject(PHPParallelLintCommand::class);
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
        $localSubject = Mockery::mock(PHPParallelLintCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sca:parallel-lint');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run Parallel-Lint on PHP files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool executes Parallel-Lint on a certain set of PHP files of this Project. It '
                . 'ignores files which are in directories with a .dontLintPHP file. Subdirectories are ignored too.');
        $localSubject->configure();
    }

    /**
     * @test
     */
    public function writeViolationsToOutput()
    {
        $mockedTargetBranch     = '';
        $mockedProcessIsolation = true;
        $expectedExitCode       = 0;

        $this->prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation);

        $this->subjectParameters[PHPParallelLintAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
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
    private function prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation)
    {
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(false);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('process-isolation')->andReturn($mockedProcessIsolation);
    }
}
