<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;
use function Safe\realpath;

class EnvironmentTest extends TestCase
{
    private Environment $subject;
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    /** @var MockInterface|EnhancedFileInfo  */
    private $mockedEnhancedFileInfo;

    protected function setUp(): void
    {
        $this->mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(Environment::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRootDirectory(): void
    {
        $expectedPath = dirname(__DIR__, 4);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-parse', '--show-toplevel')->andReturn($expectedPath);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(realpath(dirname(__DIR__, 4)))->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getRootDirectory();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  false
     */
    public function getVendorPath(): void
    {
        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(realpath(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'vendor'))
            ->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getVendorPath();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
    }

    /**
     * @test
     */
    public function getPackageDirectory(): void
    {
        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(dirname(__DIR__, 4))->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getPackageDirectory();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
    }

}
