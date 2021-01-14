<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPParallelLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPParallelLintAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|PHPParallelLintAdapter */
    private $partialSubject;
    private string $mockedPackageDirectory;
    private SmartFileInfo $forgedPackageDirectory;
    private string $mockedRootDirectory;
    private SmartFileInfo $forgedRootDirectory;
    /** @var MockInterface|TerminalCommandFinder */
    private $mockedTerminalCommandFinder;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $this->mockedRootDirectory = realpath(__DIR__ . '/../../../..');
        $this->forgedRootDirectory = new SmartFileInfo($this->mockedRootDirectory);
        $this->mockedPackageDirectory = realpath($this->mockedRootDirectory . '/src');
        $this->forgedPackageDirectory = new SmartFileInfo($this->mockedPackageDirectory);

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn($this->forgedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->forgedRootDirectory);

        $this->partialSubject = Mockery::mock(
            PHPParallelLintAdapter::class.'[!init]',
            [
                $this->mockedEnvironment,
                $this->mockedOutputInterface,
                $this->mockedGenericCommandRunner,
                $this->mockedTerminalCommandFinder,
            ]
        )->shouldAllowMockingProtectedMethods()->makePartial();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function constructSetsUpSubjectCorrectly(): void
    {
        self::assertSame('.dontLintPHP', $this->partialSubject->getBlacklistToken());
        self::assertSame(['.php'], $this->partialSubject->getAllowedFileEndings());
        self::assertSame('--exclude ', $this->partialSubject->getBlacklistPrefix());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());
        self::assertFalse($this->partialSubject->isEscape());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'PHPPLWL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/parallel-lint -j 2 %1$s'
                ),
                H::hasKeyValuePair(
                    'PHPPLBL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/parallel-lint -j 2 %1$s ./'
                )
            )
        );
    }

    /**
     * Data Provider for callMethodsWithParametersCallsRunToolAndReturnsResult.
     *
     * @return array
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider(): array
    {
        return [
            'find Violations' => [
                'tool' => 'PHPPL',
                'fullMessage' => 'PHPPL : Running full check',
                'diffMessage' => 'PHPPL : Running check on diff',
                'method' => 'writeViolationsToOutput',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider
     *
     * @param string $tool
     * @param string $fullMessage
     * @param string $diffMessage
     * @param string $method
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResult(
        string $tool,
        string $fullMessage,
        string $diffMessage,
        string $method
    ): void {
        $mockedProcessIsolation = true;
        $mockedTargetBranch = 'myTargetBranch';
        $expectedResult = 123123123;

        $this->partialSubject->shouldReceive('runTool')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation, $fullMessage, $tool, $diffMessage)
            ->andReturn($expectedResult);

        $result = $this->partialSubject->$method($mockedTargetBranch, $mockedProcessIsolation);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function phpCodeSnifferAdapterimplementsInterface(): void
    {
        self::assertNotInstanceOf(FixerSupportInterface::class, $this->partialSubject);
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
