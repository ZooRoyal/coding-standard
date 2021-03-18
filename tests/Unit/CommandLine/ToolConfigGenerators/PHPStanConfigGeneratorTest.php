<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolConfigGenerators;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\CouldNotWriteFileException;
use PHPStan\File\FileWriter;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\ToolConfigGeneratorInterface;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class PHPStanConfigGeneratorTest extends TestCase
{
    /** @var MockInterface|NeonAdapter */
    private $mockedNeonAdapter;
    /** @var MockInterface|FileWriter */
    private $mockedFileWriter;
    /** @var MockInterface| ExclusionListFactory */
    private $mockedBlacklistFactory;
    /** @var PHPStanConfigGenerator */
    private $subject;

    protected function setUp(): void
    {
        $this->mockedNeonAdapter = Mockery::mock(NeonAdapter::class);
        $this->mockedFileWriter = Mockery::mock(FileWriter::class);
        $this->mockedBlacklistFactory = Mockery::mock(ExclusionListFactory::class);

        $this->subject = new PHPStanConfigGenerator(
            $this->mockedNeonAdapter,
            $this->mockedFileWriter,
            $this->mockedBlacklistFactory
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
    public function addConfigParameters(): void
    {
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $this->mockedBlacklistFactory->shouldReceive('build')->once()
            ->with('.dontStanPHP')->andReturn([$mockedEnhancedFileInfo]);
        $mockedEnhancedFileInfo->shouldReceive('getRealPath')->once()
            ->withNoArgs()->andReturn('vendor');

        $params = $this->subject->addConfigParameters(
            '.dontStanPHP',
            ['includes' => ['/blala/lalal']]
        );

        MatcherAssert::assertThat(
            $params,
            Matchers::allOf(
                Matchers::hasKeyValuePair(
                    'parameters',
                    Matchers::allOf(
                        Matchers::hasKeyValuePair('excludes_analyse', Matchers::hasValue('vendor'))
                    )
                ),
                Matchers::hasKeyValuePair('includes', Matchers::hasValue('/blala/lalal'))
            )
        );
    }

    /**
     * @test
     */
    public function generateConfig(): void
    {
        $params = ['parameters' => ['excludes_analyse' => ['vendor'], ['includes' => '/blala/lalal']]];
        $this->mockedNeonAdapter->shouldReceive('dump')->once()->with($params)->andReturn('neonstring');
        $configString = $this->subject->generateConfig($params);
        self::assertEquals('neonstring', $configString);
    }

    /**
     * @test
     */
    public function writeConfig(): void
    {
        $this->mockedFileWriter->shouldReceive('write')->withArgs(
            [
                'config/phpstan/phpstan.neon',
                'neonconfig',
            ]
        )->once();
        $this->subject->writeConfig('config/phpstan/phpstan.neon', 'neonconfig');
    }

    /**
     * @test
     */
    public function writeConfigWithThrownException(): void
    {
        $this->expectException(CouldNotWriteFileException::class);
        $this->mockedFileWriter->shouldReceive('write')->once()->withArgs([' ', 'neonconfig'])
            ->andThrow(CouldNotWriteFileException::class);

        $this->subject->writeConfig(' ', 'neonconfig');
    }

    /**
     * @test
     */
    public function phpCodeSnifferConfigToolGeneratorimplementsInterface(): void
    {
        self::assertInstanceOf(ToolConfigGeneratorInterface::class, $this->subject);
    }
}
