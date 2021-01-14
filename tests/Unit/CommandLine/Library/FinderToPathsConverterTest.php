<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class FinderToPathsConverterTest extends TestCase
{
    private FinderToPathsConverter $subject;
    private array $subjectParameters;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(FinderToPathsConverter::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    /**
     * @test
     */
    public function finderToArray(): void
    {
        $forgedFinder = new Finder();
        $forgedFinder->in(__DIR__);
        $forgedSmartFileInfo = new SmartFileInfo(__FILE__);
        $expectedResult = [$forgedSmartFileInfo];

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('sanitizeArray')->once()
            ->with(
                H::both(
                    H::everyItem(H::anInstanceOf(SplFileInfo::class))
                )->andAlso(
                    H::hasItem(
                        HasProperty::hasProperty('getRealPath', __FILE__)
                    )
                )->andAlso(
                    H::hasItem(
                        HasProperty::hasProperty('getRealPath', H::startsWith(__DIR__ . '/Exceptions'))
                    )
                )
            )->andReturn(['la' => $forgedSmartFileInfo, 'lu' => $forgedSmartFileInfo]);

        $result = $this->subject->finderToArray($forgedFinder);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function finderToArrayOfDirectories(): void
    {
        $forgedFinder = new Finder();
        $forgedFinder->in(__DIR__);
        $forgedSmartFileInfo = new SmartFileInfo(__FILE__);

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')->once()
            ->with(H::arrayContainingInAnyOrder([__DIR__, __DIR__ . '/Exceptions']))
            ->andReturn([$forgedSmartFileInfo]);

        $result = $this->subject->finderToArrayOfDirectories($forgedFinder);

        self::assertSame([$forgedSmartFileInfo], $result);
    }
}
