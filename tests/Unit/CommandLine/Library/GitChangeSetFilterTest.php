<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\LogicException;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitChangeSetFilterTest extends TestCase
{
    private GitChangeSetFilter $subject;
    /** @var MockInterface[] */
    private array $subjectParameters;
    private string $blacklistedDirectory = 'tests';

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
        $forgedWhitelistedDirectory = new SmartFileInfo('vendor');
        $forgedSrc = new SmartFileInfo('src');
        $forgedConfig = new SmartFileInfo('config');
        $forgedGitChangeSet = new GitChangeSet(
            [
                new SmartFileInfo($this->blacklistedDirectory . '/Unit'),
                $forgedWhitelistedDirectory,
                $forgedSrc,
                $forgedConfig,
            ],
            'asdaqwe212123'
        );
        $expectedResult = [$forgedWhitelistedDirectory, $forgedSrc, $forgedConfig,];
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn([new SmartFileInfo($this->blacklistedDirectory)]);
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('findTokenDirectories')
            ->once()->with($whitelistToken)->andReturn([$forgedWhitelistedDirectory]);

        $this->subject->filter($forgedGitChangeSet, [], $blacklistToken, $whitelistToken);

        MatcherAssert::assertThat(
            $forgedGitChangeSet->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithoutFilter(): void
    {
        $forgedSrc = new SmartFileInfo('src');
        $forgedConfig = new SmartFileInfo('config');
        $forgedGitChangeSet = new GitChangeSet(
            [
                new SmartFileInfo($this->blacklistedDirectory . '/Unit'),
                $forgedSrc,
                $forgedConfig,
            ],
            'asdaqwe212123'
        );
        $expectedResult = [$forgedSrc, $forgedConfig];
        $blackListToken = 'stopMe';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blackListToken, true)->andReturn([new SmartFileInfo($this->blacklistedDirectory)]);

        $this->subject->filter($forgedGitChangeSet, [], $blackListToken);
        MatcherAssert::assertThat(
            $forgedGitChangeSet->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithFilter(): void
    {
        $mockedFilter = ['php', 'xml'];
        $forgedPhpFile = new SmartFileInfo(__FILE__);
        $forgedXMLFile = new SmartFileInfo('phpunit.xml');
        $forgedFileToBeFiltered = new SmartFileInfo('composer.json');

        $forgedGitChangeSet = new GitChangeSet(
            [$forgedPhpFile, $forgedXMLFile, $forgedFileToBeFiltered],
            'asdaqwe212123'
        );

        $expectedResult = [$forgedPhpFile, $forgedXMLFile];

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with('', true)->andReturn([]);

        $this->subject->filter($forgedGitChangeSet, $mockedFilter);
        MatcherAssert::assertThat(
            $forgedGitChangeSet->getFiles(),
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

        /** @var MockInterface|GitChangeSet $mockedGitChangeSet */
        $mockedGitChangeSet = Mockery::mock(GitChangeSet::class);
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn(['hallo']);
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('findTokenDirectories')
            ->once()->with($whitelistToken)->andReturn(['hallo']);

        $this->subject->filter($mockedGitChangeSet, [], $blacklistToken, $whitelistToken);
    }
}
