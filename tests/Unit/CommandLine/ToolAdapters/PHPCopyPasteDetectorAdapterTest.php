<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCopyPasteDetectorAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPCopyPasteDetectorAdapterTest extends TestCase
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
    /** @var string */
    private $expectedPrefix;
    /** @var string */
    private $expectedGlue;

    protected function setUp()
    {
        $this->mockedEnvironment          = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface      = Mockery::mock(OutputInterface::class);

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory    = '/root/directory';

        $this->mockedProcessisolation = true;
        $this->expectedExitCode       = 0;
        $this->expectedStopword       = '.dontCopyPasteDetectPHP';
        $this->expectedFilter         = '.php';
        $this->expectedPrefix         = '--exclude ';
        $this->expectedGlue           = ' ';

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
    public function writeViolationsToOutputWithTargetForBlacklistCheck()
    {
        $mockedTargetBranch = 'targetBranch';
        $expectedCommand    ='php ' . $this->mockedRootDirectory . '/vendor/bin/phpcpd -vvv --progress --fuzzy -n ' .
            '--names-exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php %1$s ' . $this->mockedRootDirectory;

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('PHPCPD: Running full check', OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($expectedCommand, $this->expectedStopword, $this->expectedPrefix, $this->expectedGlue)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }
}
