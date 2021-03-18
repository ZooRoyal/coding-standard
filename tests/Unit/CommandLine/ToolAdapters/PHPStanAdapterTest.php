<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use ComposerLocator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPStanAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;

class PHPStanAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|PHPStanAdapter */
    private $partialSubject;
    /** @var MockInterface|PHPStanConfigGenerator */
    private $mockedPHPStanConfigGenerator;
    /** @var string */
    private $mockedPackageDirectory;
    /** @var string */
    private $mockedRootDirectory;
    /** @var Mockery\LegacyMockInterface|MockInterface|TerminalCommandFinder */
    private $mockedTerminalCommandFinder;
    /** @var string */
    private $mockedVendorDirectory;
    /** @var array<string,string> */
    private array $toolFunctionsFileMapping
        = [
            'hamcrest/hamcrest-php' => '/hamcrest/Hamcrest.php',
            'sebastianknott/hamcrest-object-accessor' => '/src/functions.php',
            'mockery/mockery' => '/library/helpers.php',
        ];

    protected function setUp(): void
    {
        $this->prepareMockedEnvironment();
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);
        $this->mockedPHPStanConfigGenerator = Mockery::mock(PHPStanConfigGenerator::class);

        $this->partialSubject = Mockery::mock(
            PHPStanAdapter::class . '[!init]',
            [
                $this->mockedEnvironment,
                $this->mockedOutputInterface,
                $this->mockedGenericCommandRunner,
                $this->mockedTerminalCommandFinder,
                $this->mockedPHPStanConfigGenerator,
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
        $config = '/config/phpstan/phpstan.neon';
        self::assertSame('.dontStanPHP', $this->partialSubject->getBlacklistToken());
        self::assertSame(['.php'], $this->partialSubject->getAllowedFileEndings());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'PHPStanBL',
                    'php ' . $this->mockedVendorDirectory . '/bin/phpstan analyse --no-progress --error-format=github '
                    . $this->mockedRootDirectory . ' -c ' . $this->mockedPackageDirectory . $config
                ),
                H::hasKeyValuePair(
                    'PHPStanWL',
                    'php ' . $this->mockedVendorDirectory
                    . '/bin/phpstan analyse --no-progress --error-format=github -c '
                    . $this->mockedPackageDirectory . $config . ' %1$s'
                )
            )
        );
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResult(): void
    {
        $this->prepareComposerLocator();

        $mockedTargetBranch = 'myTargetBranch';
        $expectedResult = 123123123;
        $tool = 'PHPStan';
        $fullMessage = 'PHPStan : Running full check';
        $diffMessage = 'PHPStan : Running check on diff';
        $method = 'writeViolationsToOutput';

        $this->mockedPHPStanConfigGenerator->shouldReceive('addConfigParameters')->once()
            ->withArgs(
                [
                    '.dontStanPHP',
                    ['includes' => ['/package/directory/config/phpstan/phpstan.neon.dist']],
                ]
            )->andReturn(['config']);
        $this->mockedPHPStanConfigGenerator->shouldReceive('generateConfig')
            ->once()->with([0 => 'config'])->andReturn('test');
        $this->mockedPHPStanConfigGenerator->shouldReceive('writeConfig')->once()->with(
            '/package/directory/config/phpstan/phpstan.neon',
            'test'
        );

        $this->partialSubject->shouldReceive('runTool')->once()
            ->with($mockedTargetBranch, $fullMessage, $tool, $diffMessage)
            ->andReturn($expectedResult);

        $result = $this->partialSubject->$method($mockedTargetBranch);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function writeConfigFileAddsCorrectTools(): void
    {
        $mockedTargetBranch = 'myTargetBranch';
        $expectedResult = 123123123;
        $expectedRootPath = 'blabla/';
        $mockedConfig = ['includes' => ['/package/directory/config/phpstan/phpstan.neon.dist']];

        $mockedComposerLocator = Mockery::mock('overload:' . ComposerLocator::class);

        foreach ($this->toolFunctionsFileMapping as $toolName => $functionsFile) {
            $mockedComposerLocator->shouldReceive('getPath')
                ->with($toolName)->andReturn($expectedRootPath . $toolName);
            $mockedConfig['parameters']['bootstrapFiles'][] = $expectedRootPath . $toolName . $functionsFile;
        }
        $this->mockedPHPStanConfigGenerator->shouldIgnoreMissing();

        $this->mockedPHPStanConfigGenerator->shouldReceive('addConfigParameters')->once()
            ->with('.dontStanPHP', $mockedConfig)->andReturn(['config']);

        $this->partialSubject->shouldReceive('runTool')->once()->andReturn($expectedResult);

        $result = $this->partialSubject->writeViolationsToOutput($mockedTargetBranch);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function phpCodeSnifferAdapterimplementsInterface(): void
    {
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }

    private function prepareComposerLocator(): void
    {
        $mockedComposerLocator = Mockery::mock('overload:' . ComposerLocator::class);

        foreach ($this->toolFunctionsFileMapping as $toolName => $functionsFile) {
            $mockedComposerLocator->shouldReceive('getPath')
                ->with($toolName)->andThrow(new RuntimeException());
            $this->mockedOutputInterface->shouldReceive('writeln')->once()
                ->with(
                    '<info>' . $toolName . ' not found. Skip loading ' . $functionsFile . '</info>',
                    OutputInterface::VERBOSITY_VERBOSE
                );
        }
    }

    /**
     * Instantiates mock object for environment dependency with default behavior.
     */
    private function prepareMockedEnvironment(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);

        $this->mockedVendorDirectory = '/I/Am/The/Vendor';
        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';

        $this->mockedEnvironment->shouldReceive('getVendorPath->getRealPath')
            ->withNoArgs()->andReturn('' . $this->mockedVendorDirectory);
        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRealPath')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);
    }
}
