<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCopyPasteDetectorAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPCopyPasteDetectorAdapterTest extends TestCase
{
    /** @var PHPCopyPasteDetectorAdapter */
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
    /** @var int */
    private $expectedExitCode;
    /** @var string */
    private $expectedPrefix;
    /** @var string */
    private $expectedGlue;
    /** @var string  */
    private $mockedVendorDirectory;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->mockedVendorDirectory = '/I/Am/The/Vendor';
        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';

        $this->expectedExitCode = 0;
        $this->expectedStopword = '.dontCopyPasteDetectPHP';
        $this->expectedPrefix = '--exclude ';
        $this->expectedGlue = ' ';

        $this->mockedEnvironment->shouldReceive('getVendorPath')
            ->withNoArgs()->andReturn('' . $this->mockedVendorDirectory);
        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

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
    public function writeViolationsToOutputWithTargetForBlacklistCheck()
    {
        $mockedTargetBranch = 'targetBranch';
        $expectedCommand = 'php ' . $this->mockedVendorDirectory . '/bin/phpcpd --fuzzy ' .
            '--exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php %1$s ' . $this->mockedRootDirectory;

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('PHPCPD : Running full check', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($expectedCommand, $this->expectedStopword, $this->expectedPrefix, $this->expectedGlue)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch);

        self::assertSame($this->expectedExitCode, $result);
    }
}
