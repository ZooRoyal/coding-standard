<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinder;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\Excluders\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSet;
use Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSetFilter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitChangeSetFilterTest extends TestCase
{
    private GitChangeSetFilter $subject;
    /** @var array<MockInterface> */
    private array $subjectParameters;
    private string $exclusionlistedDirectory = 'blub';
    private string $mockedRootDirectory = '/my/root/directory';

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitChangeSetFilter::class);
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subject = $buildFragments['subject'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function filterByBlacklistAndWhitelistWithoutFilter(): void
    {
        $whitelistedDirectory = $this->exclusionlistedDirectory . '/wumpe';
        $whitelistedFile = $whitelistedDirectory . '/binNochDa';
        $expectedResult = $this->prepareMockedEnhancedFileInfo(['wahwah', 'bla', $whitelistedFile]);
        $mockedFileList = new GitChangeSet(
            array_merge(
                $this->prepareMockedEnhancedFileInfo([$this->exclusionlistedDirectory . '/sowas']),
                $expectedResult
            ),
            'asdaqwe212123'
        );
        $exclusionlistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with($exclusionlistToken, false)->andReturn(
                $this->prepareMockedEnhancedFileInfo([$this->exclusionlistedDirectory])
            );
        $this->subjectParameters[TokenExcluder::class]->shouldReceive('getPathsToExclude')
            ->once()->with([], ['token' => $whitelistToken])->andReturn(
                $this->prepareMockedEnhancedFileInfo([$whitelistedDirectory])
            );

        $this->subject->filter($mockedFileList, [], $exclusionlistToken, $whitelistToken);

        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithoutFilter(): void
    {
        $blackListToken = 'stopMe';
        $expectedResult = $this->prepareMockedEnhancedFileInfo(['wahwah', 'bla']);
        $mockedInput = array_merge(
            $this->prepareMockedEnhancedFileInfo([$this->exclusionlistedDirectory . '/sowas']),
            $expectedResult
        );
        $forgedBlacklistedDirectories = $this->prepareMockedEnhancedFileInfo([$this->exclusionlistedDirectory]);
        $forgedGitChangeSet = new GitChangeSet($mockedInput, 'asdaqwe212123');

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with($blackListToken, true)->andReturn($forgedBlacklistedDirectories);

        $this->subject->filter($forgedGitChangeSet, [], $blackListToken);
        $result = $forgedGitChangeSet->getFiles();

        self::assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithFilter(): void
    {
        $mockedFilter = ['wahwah', 'wubwub'];
        $expectedResult = $this->prepareMockedEnhancedFileInfo(
            [
                'asd' . $mockedFilter[0],
                'qweqweq' . $mockedFilter[1],
            ]
        );
        $mockedFileList = new GitChangeSet(
            array_merge(
                $this->prepareMockedEnhancedFileInfo(
                    [
                        $this->exclusionlistedDirectory . '/mussWeg',
                        $mockedFilter[1] . 'FalsePositive',
                    ]
                ),
                $expectedResult
            ),
            'asdaqwe212123'
        );

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with('', true)->andReturn($this->prepareMockedEnhancedFileInfo([$this->exclusionlistedDirectory]));

        $this->subject->filter($mockedFileList, $mockedFilter);
        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterThrowsExceptionIfBlackAndWhitelisted(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionCode('1553780055');

        /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\FileFinder\GitChangeSet $mockedFileList */
        $mockedFileList = Mockery::mock(GitChangeSet::class);
        $exclusionlistToken = 'stopMe';
        $whitelistToken = 'neverMind';
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with($exclusionlistToken, false)->andReturn([$mockedEnhancedFileInfo]);
        $this->subjectParameters[TokenExcluder::class]->shouldReceive('getPathsToExclude')
            ->once()->with([], ['token' => $whitelistToken])->andReturn([$mockedEnhancedFileInfo]);

        $this->subject->filter($mockedFileList, [], $exclusionlistToken, $whitelistToken);
    }

    /**
     * Creates preconfigured Mockery mocks of EnhancedFileInfo for given Paths.
     *
     * @param array<string> $filePaths
     *
     * @return array<MockInterface|\Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo>
     */
    private function prepareMockedEnhancedFileInfo(array $filePaths): array
    {
        $enhancedFileMocks = [];
        foreach ($filePaths as $filePath) {
            $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);
            $mockedEnhancedFileInfo->shouldReceive('getRelativePathname')
                ->withNoArgs()->andReturn($filePath);
            $mockedEnhancedFileInfo->shouldReceive('getRealPath')
                ->withNoArgs()->andReturn($this->mockedRootDirectory . '/' . $filePath);
            $mockedEnhancedFileInfo->shouldReceive('endsWith')
                ->andReturnUsing(fn($suffix) => str_ends_with($this->mockedRootDirectory . '/' . $filePath, $suffix));
            $mockedEnhancedFileInfo->shouldReceive('startsWith')
                ->andReturnUsing(fn($prefix) => str_starts_with($this->mockedRootDirectory . '/' . $filePath, $prefix));
            $enhancedFileMocks[] = $mockedEnhancedFileInfo;
        }
        return $enhancedFileMocks;
    }
}
