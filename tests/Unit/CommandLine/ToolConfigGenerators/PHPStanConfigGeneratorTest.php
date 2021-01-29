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
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\ToolConfigGeneratorInterface;

class PHPStanConfigGeneratorTest extends TestCase
{
    /** @var MockInterface|NeonAdapter */
    private $mockedNeonAdapter;
    /** @var MockInterface|FileWriter */
    private $mockedFileWriter;
    /** @var MockInterface| BlacklistFactory */
    private $mockedBlacklistFactory;
    /** @var PHPStanConfigGenerator */
    private $subject;

    protected function setUp(): void
    {
        $this->mockedNeonAdapter = Mockery::mock(NeonAdapter::class);
        $this->mockedFileWriter = Mockery::mock(FileWriter::class);
        $this->mockedBlacklistFactory = Mockery::mock(BlacklistFactory::class);
        $this->subject = Mockery::mock(PHPStanConfigGenerator::class);

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
    public function testAddConfigParameters()
    {
        $this->mockedBlacklistFactory->shouldReceive('build')->once()->with('.dontStanPHP')->andReturn(['vendor']);
        $params = $this->subject->addConfigParameters('.dontStanPHP', '', ['includes' => ['/blala/lalal']]);

        MatcherAssert::assertThat(
            $params,
            Matchers::allOf(
                Matchers::hasKeyValuePair(
                    'parameters',
                    Matchers::allOf(
                        Matchers::hasKeyValuePair('excludes_analyse', Matchers::hasValue('/vendor'))
                    )
                ),
                Matchers::hasKeyValuePair('includes', Matchers::hasValue('/blala/lalal'))
            )
        );
    }

    /**
     * @test
     */
    public function testGenerateConfig()
    {
        $params = ['parameters' => ['excludes_analyse' => ['vendor'], ['includes' => '/blala/lalal']]];
        $this->mockedNeonAdapter->shouldReceive('dump')->once()->with($params)->andReturn('neonstring');
        $configString = $this->subject->generateConfig($params);
        self::assertEquals('neonstring', $configString);
    }

    /**
     * @test
     */
    public function testWriteConfig()
    {
        $this->mockedFileWriter->shouldReceive('write')->withArgs([
            'config/phpstan/phpstan.neon',
            'neonconfig',
        ])->once();
        $this->subject->writeConfig('config/phpstan/phpstan.neon', 'neonconfig');
    }

    /**
     * @test
     */
    public function testWriteConfigWithThrownException()
    {
        $this->expectException(CouldNotWriteFileException::class);
        $this->mockedFileWriter->shouldReceive('write')->once()->withArgs([' ', 'neonconfig'])
            ->andThrow(CouldNotWriteFileException::class);

        $this->subject->writeConfig(' ', 'neonconfig');
    }

    /**
     * @test
     */
    public function phpCodeSnifferConfigToolGeneratorimplementsInterface()
    {
        self::assertInstanceOf(ToolConfigGeneratorInterface::class, $this->subject);
    }
}
