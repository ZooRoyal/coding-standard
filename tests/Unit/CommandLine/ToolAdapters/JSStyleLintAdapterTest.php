<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class JSStyleLintAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|JSStyleLintAdapter */
    private $partialSubject;
    private string $mockedPackageDirectory;
    private string $forgedCommandPath;
    /** @var MockInterface|TerminalCommandFinder */
    private $mockedTerminalCommandFinder;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $this->forgedCommandPath = 'wubwubwub';
        $this->mockedPackageDirectory = '/package/directory';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->withNoArgs()->andReturn($this->mockedPackageDirectory);
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('stylelint')->andReturn($this->forgedCommandPath)->byDefault();

        $this->partialSubject = Mockery::mock(
            JSStyleLintAdapter::class . '[!init]',
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
        $expectedFilter = '/*.{css,scss,sass,less}';
        $config = '/config/stylelint/.stylelintrc';
        self::assertSame('.dontSniffLESS', $this->partialSubject->getBlacklistToken());
        self::assertSame([$expectedFilter], $this->partialSubject->getAllowedFileEndings());
        self::assertSame('--ignore-pattern=', $this->partialSubject->getBlacklistPrefix());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'STYLELINTWL',
                    $this->forgedCommandPath . ' %1$s --allow-empty-input --config='
                    . $this->mockedPackageDirectory . $config
                ),
                H::hasKeyValuePair(
                    'STYLELINTFIXWL',
                    $this->forgedCommandPath . ' %1$s --allow-empty-input --config='
                    . $this->mockedPackageDirectory . $config . ' --fix'
                ),
                H::hasKeyValuePair(
                    'STYLELINTBL',
                    $this->forgedCommandPath . ' **' . $expectedFilter
                    . ' --allow-empty-input --config=' . $this->mockedPackageDirectory . $config . ' %1$s'
                ),
                H::hasKeyValuePair(
                    'STYLELINTFIXBL',
                    $this->forgedCommandPath . ' **' . $expectedFilter
                    . ' --allow-empty-input --config=' . $this->mockedPackageDirectory . $config . ' --fix %1$s'
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
                'tool' => 'STYLELINT',
                'fullMessage' => 'STYLELINT : Running full check',
                'diffMessage' => 'STYLELINT : Running check on diff',
                'method' => 'writeViolationsToOutput',
            ],
            'fix Violations' => [
                'tool' => 'STYLELINTFIX',
                'fullMessage' => 'STYLELINTFIX : Fix all Files',
                'diffMessage' => 'STYLELINTFIX : Fix Files in diff',
                'method' => 'fixViolations',
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
        $mockedTargetBranch = 'myTargetBranch';
        $expectedResult = 123123123;

        $this->partialSubject->shouldReceive('runTool')->once()
            ->with($mockedTargetBranch, $fullMessage, $tool, $diffMessage)
            ->andReturn($expectedResult);

        $result = $this->partialSubject->$method($mockedTargetBranch);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function skipWriteViolationsWritesWarningToOutputIfEsLintIsNotFound(): void
    {
        $mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('stylelint')->andThrow(new TerminalCommandNotFoundException());

        $mockedOutputInterface->shouldReceive('write')->once()
            ->with(H::containsString('StyleLint could not be found'), true);

        /** @var MockInterface|JSStyleLintAdapter $partialSubject */
        $partialSubject = Mockery::mock(
            JSStyleLintAdapter::class . '[!init]',
            [$this->mockedEnvironment, $mockedOutputInterface, $mockedGenericCommandRunner,
            $mockedTerminalCommandFinder]
        )->shouldAllowMockingProtectedMethods()->makePartial();

        $result = $partialSubject->writeViolationsToOutput('asd');

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function fixViolationsWritesWarningToOutputIfEsLintIsNotFound(): void
    {
        $mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('stylelint')->andThrow(new TerminalCommandNotFoundException());

        $mockedOutputInterface->shouldReceive('write')->once()
            ->with(H::containsString('StyleLint could not be found'), true);

        /** @var MockInterface|JSStyleLintAdapter $partialSubject */
        $partialSubject = Mockery::mock(
            JSStyleLintAdapter::class . '[!init]',
            [$this->mockedEnvironment, $mockedOutputInterface, $mockedGenericCommandRunner, $mockedTerminalCommandFinder]
        )->shouldAllowMockingProtectedMethods()->makePartial();

        $result = $partialSubject->fixViolations('asd');

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function phpCodeSnifferAdapterimplementsInterface(): void
    {
        self::assertInstanceOf(FixerSupportInterface::class, $this->partialSubject);
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
