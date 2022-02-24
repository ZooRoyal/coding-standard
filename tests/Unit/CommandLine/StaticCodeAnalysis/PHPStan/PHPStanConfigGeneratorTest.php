<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPStan;

use ComposerLocator;
use Hamcrest\Matcher;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPStan\DependencyInjection\NeonAdapter;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\PHPStanConfigGenerator;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class PHPStanConfigGeneratorTest extends TestCase
{
    private PHPStanConfigGenerator $subject;
    /** @var MockInterface|NeonAdapter */
    private NeonAdapter $mockedNeonAdapter;
    /** @var MockInterface|Filesystem */
    private Filesystem $mockedFilesystem;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    private string $mockedPackageDirectory = '/tmp/phpunitTest';
    private string $mockedRootDirectory = '/tmp';

    protected function setUp(): void
    {
        $mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedFilesystem = Mockery::mock(Filesystem::class);
        $this->mockedNeonAdapter = Mockery::mock(NeonAdapter::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $mockedEnvironment
            ->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn($this->mockedPackageDirectory);
        $mockedEnvironment
            ->shouldReceive('getRootDirectory->getRealPath')
            ->andReturn($this->mockedRootDirectory);

        $this->subject = new PHPStanConfigGenerator(
            $this->mockedNeonAdapter,
            $this->mockedFilesystem,
            $mockedEnvironment
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function getConfigPathReturnsConfigPath(): void
    {
        $result = $this->subject->getConfigPath();

        self::assertSame($this->mockedPackageDirectory . '/config/phpstan/phpstan.neon', $result);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  false
     */
    public function writeConfigFileWritesConfigFileToFilesystem(): void
    {
        $forgedHamcrestPath = '/hamcrest';
        $forgedMockeryPath = '/mockery';
        $forgedFilePath = '/asdqweqwe/ww';
        $forgedConfiguration = 'argh';
        $mockedComposerLocator = Mockery::mock('overload:' . ComposerLocator::class);
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
        $mockedExclusionList = [$mockedEnhancedFileInfo];

        $this->prepareMockedComposerLocator($mockedComposerLocator, $forgedHamcrestPath, $forgedMockeryPath);
        $this->prepareMockedFilesystem($forgedConfiguration);

        $mockedEnhancedFileInfo->shouldReceive('getRealPath')->atLeast()->once()->andReturn($forgedFilePath);


        $this->mockedOutput->shouldReceive('writeln')->times(4)->with(
            H::anyOf(
                '<info>Writing new PHPStan configuration.</info>' . PHP_EOL,
                '<info>sebastianknott/hamcrest-object-accessor not found. Skip loading /src/functions.php.</info>',
                '<info>squizlabs/php_codesniffer not found. Skip loading /autoload.php, /src/Util/Tokens.php.</info>',
                '<info>slevomat/coding-standard not found. Skip loading /autoload-bootstrap.php.</info>',
            ),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $this->mockedNeonAdapter->shouldReceive('dump')->once()
            ->with($this->buildConfigMatcher($forgedHamcrestPath, $forgedMockeryPath, $forgedFilePath))
            ->andReturn($forgedConfiguration);

        $this->subject->writeConfigFile($this->mockedOutput, $mockedExclusionList);
    }

    /**
     * This method builds the validation matcher for the configuration.
     */
    private function buildConfigMatcher(
        string $forgedHamcrestPath,
        string $forgedMockeryPath,
        string $forgedFilePath,
    ): Matcher {
        $includesMatcher = H::hasKeyValuePair(
            'includes',
            H::hasItem($this->mockedPackageDirectory . '/config/phpstan/phpstan.neon' . '.dist')
        );

        $functionsMatcher = H::hasKeyValuePair(
            'bootstrapFiles',
            H::hasItems($forgedHamcrestPath . '/hamcrest/Hamcrest.php', $forgedMockeryPath . '/library/helpers.php')
        );

        $excludesMatcher = H::hasKeyValuePair('excludePaths', H::hasItem($forgedFilePath));
        $staticDirectoriesMatcher = H::hasKeyValuePair(
            'scanDirectories',
            H::allOf(
                H::hasItem($this->mockedRootDirectory . '/Plugins'),
                H::hasItem($this->mockedRootDirectory . '/custom/project'),
            )
        );

        $parametersMatcher = H::hasKeyValuePair(
            'parameters',
            H::allOf($functionsMatcher, $excludesMatcher, $staticDirectoriesMatcher)
        );

        $matcher = H::allOf(
            $includesMatcher,
            $parametersMatcher,
        );

        return $matcher;
    }

    /**
     * Add expectations to filesystem regarding existence of static directories and writing file to disc.
     * One file directory will not be found.
     */
    private function prepareMockedFilesystem(string $forgedConfiguration): void
    {
        $this->mockedFilesystem->shouldReceive('exists')->once()
            ->with($this->mockedRootDirectory . '/Plugins')->andReturn(true);
        $this->mockedFilesystem->shouldReceive('exists')->once()
            ->with($this->mockedRootDirectory . '/custom/plugins')->andReturn(false);
        $this->mockedFilesystem->shouldReceive('exists')->once()
            ->with($this->mockedRootDirectory . '/custom/project')->andReturn(true);

        $this->mockedFilesystem->shouldReceive('dumpFile')->once()
            ->with($this->mockedPackageDirectory . '/config/phpstan/phpstan.neon', $forgedConfiguration);
    }

    /**
     * Setups the mock to find two packages and throws an exception on the third.
     */
    private function prepareMockedComposerLocator(
        MockInterface $mockedComposerLocator,
        string $forgedHamcrestPath,
        string $forgedMockeryPath,
    ): void {
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('hamcrest/hamcrest-php')->andReturn($forgedHamcrestPath);
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('sebastianknott/hamcrest-object-accessor')->andThrow(new RuntimeException());
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('mockery/mockery')->andReturn($forgedMockeryPath);
    }
}
