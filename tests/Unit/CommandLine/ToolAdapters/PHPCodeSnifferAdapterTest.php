<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;

class PHPCodeSnifferAdapterTest extends TestCase
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
        $this->expectedStopword       = '.dontSniffPHP';
        $this->expectedFilter         = '.php';
        $this->expectedGlue           = ' ';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->subject = new PHPCodeSnifferAdapter(
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
    public function phpCodeSnifferAdapterimplementsInterface()
    {
        self::assertInstanceOf(FixerSupportInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function writeViolationsToOutputWithTargetForWhitelistCheck()
    {
        $mockedTargetBranch = 'myTarget';
        $expectedCommand    = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
            . $this->mockedPackageDirectory . '/src/config/phpcs/ZooroyalDefault/ruleset.xml %1$s';

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn(false);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with('Running check on diff to ' . $mockedTargetBranch, OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runWhitelistCommand')->once()
            ->with(
                $expectedCommand,
                $mockedTargetBranch,
                $this->expectedStopword,
                $this->expectedFilter,
                true,
                $this->expectedGlue
            )
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->writeViolationsToOutput($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }


    public function writeViolationsToOutputWithTargetForBlacklistCheckDataProvider()
    {
        return [
            'local master'     => [
                'writeViolationsToOutput',
                'Running full check.',
                'expectedWrite',
                'myTarget',
                true
            ],
            'empty target'     => ['writeViolationsToOutput', 'Running full check.', 'expectedWrite',  '', false],
            'both'             => ['writeViolationsToOutput', 'Running full check.', 'expectedWrite', '', true],
            'fix local master' => ['fixViolations', 'Fix all Files.', 'expectedFix', 'myTarget', true],
            'fix empty target' => ['fixViolations', 'Fix all Files.', 'expectedFix', '', false],
            'fix both'         => ['fixViolations', 'Fix all Files.', 'expectedFix', '', true],
        ];
    }

    /**
     * @test
     *
     * @param string $method
     * @param string $message
     * @param string $command
     * @param string $mockedTargetBranch
     * @param string $equalToLocalBranch
     *
     * @dataProvider writeViolationsToOutputWithTargetForBlacklistCheckDataProvider
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function writeViolationsToOutputWithTargetForBlacklistCheck(
        $method,
        $message,
        $command,
        $mockedTargetBranch,
        $equalToLocalBranch
    ) {
        $expectedWrite = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
            . $this->mockedPackageDirectory . '/src/config/phpcs/ZooroyalDefault/ruleset.xml --ignore=%1$s '
            . $this->mockedRootDirectory;

        $expectedFix = 'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
            . $this->mockedPackageDirectory . '/src/config/phpcs/ZooroyalDefault/ruleset.xml --ignore=%1$s '
            . $this->mockedRootDirectory;

        $this->mockedEnvironment->shouldReceive('isLocalBranchEqualTo')
            ->with('origin/master')->andReturn($equalToLocalBranch);

        $this->mockedOutputInterface->shouldReceive('writeln')->once()
            ->with($message, OutputInterface::VERBOSITY_NORMAL);

        $this->mockedGenericCommandRunner->shouldReceive('runBlacklistCommand')->once()
            ->with($$command, $this->expectedStopword, '', ',', true)
            ->andReturn($this->expectedExitCode);

        $result = $this->subject->$method($mockedTargetBranch, $this->mockedProcessisolation);

        self::assertSame($this->expectedExitCode, $result);
    }

}
