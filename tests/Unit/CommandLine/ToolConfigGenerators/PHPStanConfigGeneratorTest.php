<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolConfigGenerators;

use Mockery;
use Mockery\MockInterface;
use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\CouldNotWriteFileException;
use PHPStan\File\FileWriter;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStandConfigGenerator;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\ToolConfigGeneratorInterface;

class PHPStanConfigGeneratorTest extends TestCase
{
    /** @var MockInterface|NeonAdapter */
    private $mockedNeonAdapter;
    /** @var MockInterface|FileWriter */
    private $mockedFileWriter;
    /** @var PHPStandConfigGenerator */
    private $subject;

    protected function setUp()
    {
        $this->mockedNeonAdapter = Mockery::mock(NeonAdapter::class);
        $this->mockedFileWriter = Mockery::mock(FileWriter::class);
        $this->subject = Mockery::mock(PHPStandConfigGenerator::class);

        $this->subject = new PHPStandConfigGenerator($this->mockedNeonAdapter, $this->mockedFileWriter);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testGenerateConfig()
    {
        $params = ['parameters' => ['excludes_analyse' => ['vendor']]];
        $this->mockedNeonAdapter->shouldReceive('dump')->once()->with($params)->andReturn('neonstring');
        $configString = $this->subject->generateConfig($params);
        self::assertEquals('neonstring', $configString);
    }

    /**
     * @test
     */
    public function testWriteConfig()
    {
        $this->mockedFileWriter->shouldReceive('write')->withArgs(['config/phpstan/phpstan.neon.dist', 'neonconfig'])->once();
        $this->subject->writeConfig('config/phpstan/phpstan.neon.dist', 'neonconfig');
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
