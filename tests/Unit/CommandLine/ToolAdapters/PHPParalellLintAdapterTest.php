<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPParallelLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPParalellLintAdapterTest extends TestCase
{

    /** @var PHPParallelLintAdapter */
    private $subject;
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var string */
    private $mockedPackageDirectory;
    /** @var string */
    private $mockedRootDirectory;
    /** @var string */
    private $expectedStopword;
    /** @var string */
    private $expectedFilter;
    /** @var int */
    private $expectedExitCode;
    /** @var bool */
    private $mockedProcessisolation;
    /** @var string */
    private $expectedGlue;
    /** @var string */
    private $expectedPrefix;

    protected function setUp()
    {
        $this->mockedEnvironment          = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface      = Mockery::mock(OutputInterface::class);

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory    = '/root/directory';

        $this->mockedProcessisolation = true;
        $this->expectedExitCode       = 0;
        $this->expectedStopword       = '.dontLintPHP';
        $this->expectedFilter         = '.php';
        $this->expectedPrefix         = '--exclude ';
        $this->expectedGlue           = ' ';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->subject = new PHPParallelLintAdapter(
            $this->mockedEnvironment,
            $this->mockedOutputInterface,
            $this->mockedGenericCommandRunner
        );
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function subjectImplementsToolAdapterInterface()
    {
        self::assertInstanceOf(ToolAdapterInterface::class, $this->subject);
    }

    public function writeViolationsToOutputWithTargetForWhitelistCheckDataProvider()
    {
        return [
            'with target not matching origin/master' => ['target' => 'myTarget'],
            'with empty target'                      => ['target' => null],
        ];
    }

    /**
     * @test
     * @dataProvider writeViolationsToOutputWithTargetForWhitelistCheckDataProvider
     */
    public function writeViolationsToOutputWithTargetForWhitelistCheck($target)
    {
        $expectedCommand    = 'php ' . $this->mockedRootDirectory . '/vendor/bin/parallel-lint -j 2 %1$s';

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('PHPPL: Running check on diff', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runWhitelistCommand')->once()
            ->with(
                $expectedCommand,
                $target,
                $this->expectedStopword,
                $this->expectedFilter,
                true,
                $this->expectedGlue
            )
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($target, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }

    public function writeViolationsToOutputWithTargetForBlacklistCheckDataProvider()
    {
        return [
            'local master' => ['myTarget', true],
            'no target'    => ['', true],
            'both'         => ['', true],
        ];
    }

    /**
     * @test
     * @dataProvider writeViolationsToOutputWithTargetForBlacklistCheckDataProvider
     */
    public function writeViolationsToOutputWithTargetForBlacklistCheck($mockedTargetBranch, $equalToLocal)
    {
        $expectedCommand = 'php ' . $this->mockedRootDirectory . '/vendor/bin/parallel-lint -j 2 %1$s ./';

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn($equalToLocal);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('PHPPL: Running full check', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($expectedCommand, $this->expectedStopword, $this->expectedPrefix, $this->expectedGlue)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }
}
