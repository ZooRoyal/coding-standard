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
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSESLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

/**
 * Class JSESLintAdapterTest
 */
class JSESLintAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|JSESLintAdapter */
    private $partialSubject;
    /** @var string */
    private $mockedPackageDirectory;
    /** @var string */
    private $mockedRootDirectory;
    /** @var string */
    private $forgedCommandPath;
    /** @var string[] */
    private $allowedFileEndings = ['js', 'ts', 'jsx', 'tsx'];
    /** @var MockInterface|TerminalCommandFinder */
    private $mockedTerminalCommandFinder;

    protected function setUp()
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';
        $this->forgedCommandPath = 'wubwubwub';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);
        $this->mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('eslint')->andReturn($this->forgedCommandPath)->byDefault();

        $this->partialSubject = Mockery::mock(
            JSESLintAdapter::class . '[!init]',
            [
                $this->mockedEnvironment,
                $this->mockedOutputInterface,
                $this->mockedGenericCommandRunner,
                $this->mockedTerminalCommandFinder,
            ]
        )->shouldAllowMockingProtectedMethods()->makePartial();
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function constructSetsUpSubjectCorrectly()
    {
        $configFile = '/config/eslint/.eslintrc.js';

        self::assertSame('.dontSniffJS', $this->partialSubject->getBlacklistToken());
        self::assertSame($this->allowedFileEndings, $this->partialSubject->getAllowedFileEndings());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());
        self::assertFalse($this->partialSubject->isEscape());

        $commandOptions = '--ext ' . implode(' --ext ', $this->allowedFileEndings);
        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'ESLINTBL',
                    $this->forgedCommandPath . ' --config ' . $this->mockedPackageDirectory . $configFile . ' '
                    . $commandOptions . ' %1$s ' . $this->mockedRootDirectory
                ),
                H::hasKeyValuePair(
                    'ESLINTWL',
                    $this->forgedCommandPath . ' --config ' . $this->mockedPackageDirectory . $configFile . ' '
                    . $commandOptions . ' %1$s'
                ),
                H::hasKeyValuePair(
                    'ESLINTFIXBL',
                    $this->forgedCommandPath . ' --config ' . $this->mockedPackageDirectory . $configFile . ' '
                    . $commandOptions . ' --fix %1$s ' . $this->mockedRootDirectory
                ),
                H::hasKeyValuePair(
                    'ESLINTFIXWL',
                    $this->forgedCommandPath . ' --config ' . $this->mockedPackageDirectory . $configFile . ' '
                    . $commandOptions . ' --fix %1$s'
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
                'tool' => 'ESLINT',
                'fullMessage' => 'ESLINT : Running full check',
                'diffMessage' => 'ESLINT : Running check on diff',
                'method' => 'writeViolationsToOutput',
                'toolResult' => 123123123,
                'expectedResult' => 123123123,
            ],
            'fix Violations' => [
                'tool' => 'ESLINTFIX',
                'fullMessage' => 'ESLINTFIX : Fix all Files',
                'diffMessage' => 'ESLINTFIX : Fix Files in diff',
                'method' => 'fixViolations',
                'toolResult' => 123123123,
                'expectedResult' => 123123123,
            ],
            'find Violations without files to lint' => [
                'tool' => 'ESLINT',
                'fullMessage' => 'ESLINT : Running full check',
                'diffMessage' => 'ESLINT : Running check on diff',
                'method' => 'writeViolationsToOutput',
                'toolResult' => 2,
                'expectedResult' => 0,
            ],
            'fix Violations  without files to lint' => [
                'tool' => 'ESLINTFIX',
                'fullMessage' => 'ESLINTFIX : Fix all Files',
                'diffMessage' => 'ESLINTFIX : Fix Files in diff',
                'method' => 'fixViolations',
                'toolResult' => 2,
                'expectedResult' => 0,
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
     * @param int $toolResult
     * @param int $expectedResult
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResult(
        string $tool,
        string $fullMessage,
        string $diffMessage,
        string $method,
        int $toolResult,
        int $expectedResult
    ) {
        $mockedProcessIsolation = true;
        $mockedTargetBranch = 'myTargetBranch';

        $this->partialSubject->shouldReceive('runTool')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation, $fullMessage, $tool, $diffMessage)
            ->andReturn($toolResult);
        $this->mockedOutputInterface->shouldReceive('write')
            ->with(H::containsString('ignore this'), true);

        $result = $this->partialSubject->$method($mockedTargetBranch, $mockedProcessIsolation);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function skipWriteViolationsWritesWarningToOutputIfEsLintIsNotFound()
    {
        $mockedEnvironment = Mockery::mock(Environment::class);
        $mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $mockedPackageDirectory = '/package/directory';
        $mockedRootDirectory = '/root/directory';

        $mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $mockedPackageDirectory);
        $mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($mockedRootDirectory);
        $mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('eslint')->andThrow(new TerminalCommandNotFoundException());

        $mockedOutputInterface->shouldReceive('write')->once()
            ->with(H::containsString('Eslint could not be found'), true);

        /** @var MockInterface|JSESLintAdapter $partialSubject */
        $partialSubject = Mockery::mock(
            JSESLintAdapter::class . '[!init]',
            [$mockedEnvironment, $mockedOutputInterface, $mockedGenericCommandRunner, $mockedTerminalCommandFinder]
        )->shouldAllowMockingProtectedMethods()->makePartial();

        $result = $partialSubject->writeViolationsToOutput('asd', 'qwe');

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function fixViolationsWritesWarningToOutputIfEsLintIsNotFound()
    {
        $mockedEnvironment = Mockery::mock(Environment::class);
        $mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);

        $mockedPackageDirectory = '/package/directory';
        $mockedRootDirectory = '/root/directory';

        $mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $mockedPackageDirectory);
        $mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($mockedRootDirectory);
        $mockedTerminalCommandFinder->shouldReceive('findTerminalCommand')
            ->with('eslint')->andThrow(new TerminalCommandNotFoundException());

        $mockedOutputInterface->shouldReceive('write')->once()
            ->with(H::containsString('Eslint could not be found'), true);

        $partialSubject = Mockery::mock(
            JSESLintAdapter::class . '[!init]',
            [$mockedEnvironment, $mockedOutputInterface, $mockedGenericCommandRunner, $mockedTerminalCommandFinder]
        )->shouldAllowMockingProtectedMethods()->makePartial();

        /** @var MockInterface|JSESLintAdapter $partialSubject */
        $result = $partialSubject->fixViolations('asd', true);

        self::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function phpCodeSnifferAdapterImplementsInterface()
    {
        self::assertInstanceOf(FixerSupportInterface::class, $this->partialSubject);
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
