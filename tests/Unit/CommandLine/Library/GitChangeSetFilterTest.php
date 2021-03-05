<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\LogicException;
use Zooroyal\CodingStandard\CommandLine\Factories\Exclusion\TokenExcluder;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitChangeSetFilterTest extends TestCase
{
    /** @var GitChangeSetFilter */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;
    /** @var string */
    private $blacklistedDirectory = 'blub';
    /** @var string */
    private $mockedRootDirectory = '/my/root/directory';

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
        $whitelistedDirectory = $this->blacklistedDirectory . '/wumpe';
        $whitelistedFile = $whitelistedDirectory . '/binNochDa';
        $expectedResult = $this->prepareMockedEnhancedFileInfo(['wahwah', 'bla', $whitelistedFile]);
        $mockedFileList = new GitChangeSet(
            array_merge(
                $this->prepareMockedEnhancedFileInfo([$this->blacklistedDirectory . '/sowas']),
                $expectedResult
            ),
            'asdaqwe212123'
        );
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn(
                $this->prepareMockedEnhancedFileInfo([$this->blacklistedDirectory])
            );
        $this->subjectParameters[TokenExcluder::class]->shouldReceive('getPathsToExclude')
            ->once()->with([], ['token' => $whitelistToken])->andReturn(
                $this->prepareMockedEnhancedFileInfo([$whitelistedDirectory])
            );

        $this->subject->filter($mockedFileList, [], $blacklistToken, $whitelistToken);

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
            $this->prepareMockedEnhancedFileInfo([$this->blacklistedDirectory . '/sowas']),
            $expectedResult
        );
        $forgedBlacklistedDirectories = $this->prepareMockedEnhancedFileInfo([$this->blacklistedDirectory]);
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
                        $this->blacklistedDirectory . '/mussWeg',
                        $mockedFilter[1] . 'FalsePositive',
                    ]
                ),
                $expectedResult
            ),
            'asdaqwe212123'
        );

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with('', true)->andReturn($this->prepareMockedEnhancedFileInfo([$this->blacklistedDirectory]));

        $this->subject->filter($mockedFileList, $mockedFilter);
        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterThrowsExceptionIfBlackAndWhitelisted()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionCode('1553780055');

        /** @var MockInterface|GitChangeSet $mockedFileList */
        $mockedFileList = Mockery::mock(GitChangeSet::class);
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';
        $mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $this->subjectParameters[ExclusionListFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn([$mockedEnhancedFileInfo]);
        $this->subjectParameters[TokenExcluder::class]->shouldReceive('getPathsToExclude')
            ->once()->with([], ['token' => $whitelistToken])->andReturn([$mockedEnhancedFileInfo]);

        $this->subject->filter($mockedFileList, [], $blacklistToken, $whitelistToken);
    }

    /**
     * Creates preconfigured Mockery mocks of EnhancedFileInfo for given Paths.
     *
     * @param array<string> $filePaths
     *
     * @return array<MockInterface|EnhancedFileInfo>
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
