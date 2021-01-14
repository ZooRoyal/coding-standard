<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCopyPasteDetectorAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPCopyPasteDetectorAdapterTest extends TestCase
{
    private PHPCopyPasteDetectorAdapter $subject;
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    private string $mockedPackageDirectory;
    private SmartFileInfo $forgedPackageDirectory;
    private string $mockedRootDirectory;
    private SmartFileInfo $forgedRootDirectory;
    private string $expectedStopword;
    private int $expectedExitCode;
    private bool $mockedProcessisolation;
    private string $expectedPrefix;
    private string $expectedGlue;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->mockedRootDirectory = realpath(__DIR__ . '/../../../..');
        $this->forgedRootDirectory = new SmartFileInfo($this->mockedRootDirectory);
        $this->mockedPackageDirectory = realpath($this->mockedRootDirectory . '/src');
        $this->forgedPackageDirectory = new SmartFileInfo($this->mockedPackageDirectory);

        $this->mockedProcessisolation = true;
        $this->expectedExitCode = 0;
        $this->expectedStopword = '.dontCopyPasteDetectPHP';
        $this->expectedPrefix = '--exclude ';
        $this->expectedGlue = ' ';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn($this->forgedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->subject = new PHPCopyPasteDetectorAdapter(
            $this->mockedEnvironment,
            $this->mockedOutputInterface,
            $this->mockedGenericCommandRunner
        );
    }

    protected function tearDown(): void
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
    public function writeViolationsToOutputWithTargetForBlacklistCheck(): void
    {
        $mockedTargetBranch = 'targetBranch';
        $expectedCommand = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcpd --fuzzy ' .
            '--exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php %1$s ' . $this->mockedRootDirectory;

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('PHPCPD : Running full check', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($expectedCommand, $this->expectedStopword, $this->expectedPrefix, $this->expectedGlue)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }
}
