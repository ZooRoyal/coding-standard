<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPMessDetectorAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPMessDetectorAdapterTest extends TestCase
{
    /** @var PHPCodeSnifferAdapter */
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

    protected function setUp()
    {
        $this->mockedEnvironment          = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface      = Mockery::mock(OutputInterface::class);

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory    = '/root/directory';

        $this->mockedProcessisolation = true;
        $this->expectedExitCode       = 0;
        $this->expectedStopword       = '.dontMessDetectPHP';
        $this->expectedFilter         = '.php';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->subject = new PHPMessDetectorAdapter(
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

    /**
     * @test
     */
    public function writeViolationsToOutputWithTargetForWhitelistCheck()
    {
        $mockedLocalBranch  = 'myLocalBranch';
        $mockedTargetBranch = 'myTarget';
        $expectedCommand    = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpmd %1$s text '
            . $this->mockedPackageDirectory . '/src/config/phpmd/ZooRoyalDefault/phpmd.xml --suffixes php';

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('Running check on diff to ' . $mockedTargetBranch, OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runWhitelistCommand')->once()
            ->with($expectedCommand, $mockedTargetBranch, $this->expectedStopword, $this->expectedFilter, true)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }

    public function writeViolationsToOutputWithTargetForBlacklistCheckDataProvider()
    {
        return [
            'local master' => ['myTarget', true],
            'empty target' => ['', false],
            'both'         => ['', true],
        ];
    }

    /**
     * @test
     * @dataProvider writeViolationsToOutputWithTargetForBlacklistCheckDataProvider
     *
     * @param string $mockedTargetBranch
     * @param boolean $equalToLocal
     */
    public function writeViolationsToOutputWithTargetForBlacklistCheck($mockedTargetBranch, $equalToLocal)
    {
        $expectedCommand = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpmd '
            . $this->mockedRootDirectory . ' text ' . $this->mockedPackageDirectory
            . '/src/config/phpmd/ZooRoyalDefault/phpmd.xml --suffixes php --exclude %1$s';

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('master')->andReturn($equalToLocal);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('Running full check.', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($expectedCommand, $this->expectedStopword)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }
}
