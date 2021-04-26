<?php

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

    protected function setUp(): void
    {
        $mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedFilesystem = Mockery::mock(Filesystem::class);
        $this->mockedNeonAdapter = Mockery::mock(NeonAdapter::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $mockedEnvironment
            ->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn($this->mockedPackageDirectory);

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

        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('hamcrest/hamcrest-php')->andReturn($forgedHamcrestPath);
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('sebastianknott/hamcrest-object-accessor')->andThrow(new RuntimeException());
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('mockery/mockery')->andReturn($forgedMockeryPath);

        $mockedEnhancedFileInfo->shouldReceive('getRealPath')->atLeast()->once()->andReturn($forgedFilePath);

        $this->mockedOutput->shouldReceive('writeln')->once()->with(
            '<info>Writing new PHPStan configuration.</info>' . PHP_EOL,
            OutputInterface::VERBOSITY_VERBOSE
        );
        $this->mockedOutput->shouldReceive('writeln')->once()->with(
            '<info>sebastianknott/hamcrest-object-accessor not found. Skip loading /src/functions.php</info>',
            OutputInterface::VERBOSITY_VERBOSE
        );

        $this->mockedNeonAdapter->shouldReceive('dump')->once()
            ->with($this->buildConfigMatcher($forgedHamcrestPath, $forgedMockeryPath, $forgedFilePath))
            ->andReturn($forgedConfiguration);

        $this->mockedFilesystem->shouldReceive('dumpFile')->once()
            ->with($this->mockedPackageDirectory . '/config/phpstan/phpstan.neon', $forgedConfiguration);

        $this->subject->writeConfigFile($this->mockedOutput, $mockedExclusionList);
    }

    /**
     * This method builds the validation matcher for the configuration.
     *
     * @param string $forgedHamcrestPath
     * @param string $forgedMockeryPath
     * @param string $forgedFilePath
     */
    private function buildConfigMatcher(
        string $forgedHamcrestPath,
        string $forgedMockeryPath,
        string $forgedFilePath
    ): Matcher {
        $includesMatcher = H::hasKeyValuePair(
            'includes',
            H::hasItem($this->mockedPackageDirectory . '/config/phpstan/phpstan.neon' . '.dist')
        );

        $functionsMatcher = H::hasKeyValuePair(
            'bootstrapFiles',
            H::hasItems($forgedHamcrestPath . '/hamcrest/Hamcrest.php', $forgedMockeryPath . '/library/helpers.php')
        );

        $excludesMatcher = H::hasKeyValuePair('excludes_analyse', H::hasItem($forgedFilePath));
        $parametersMatcher = H::hasKeyValuePair('parameters', H::allOf($functionsMatcher, $excludesMatcher));

        $matcher = H::allOf(
            $includesMatcher,
            $parametersMatcher
        );

        return $matcher;
    }
}
