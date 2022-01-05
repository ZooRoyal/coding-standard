<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories\Exclusion;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\StaticExcluder;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class StaticExcluderTest extends TestCase
{
    private StaticExcluder $subject;
    private string $forgedRootDirectory;
    /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo */
    private $mockedEnhancedFileInfo;
    /** @var array<MockInterface> */
    private array $subjectParameters;

    protected function setUp(): void
    {
        $this->forgedRootDirectory = dirname(__DIR__, 5);
        $this->mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(StaticExcluder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
            ->once()->withNoArgs()->andReturn($this->mockedEnhancedFileInfo);
        $this->mockedEnhancedFileInfo->shouldReceive('getRealPath')->once()
            ->withNoArgs()->andReturn($this->forgedRootDirectory);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPathsToExclude(): void
    {
        $forgedRemainingPaths = ['.git', 'node_modules', 'vendor'];
        $that = $this;
        $expectedResult = array_map(
            static function ($path) use ($that): EnhancedFileInfo {
                return new EnhancedFileInfo(
                    $that->forgedRootDirectory . '/' . $path,
                    $that->forgedRootDirectory
                );
            },
            $forgedRemainingPaths
        );

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')
            ->once()->with(Matchers::hasItems(...$forgedRemainingPaths))->andReturn($expectedResult);

        $result = $this->subject->getPathsToExclude([]);
        self::assertSame($expectedResult, $result);
    }
}
