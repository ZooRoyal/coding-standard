<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitChangeSetFilter;
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

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitChangeSetFilter::class);
        $this->subjectParameters = $buildFragments['parameters'];
        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->subject = $buildFragments['subject'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function filterByBlacklistAndWhitelistWithoutFilter()
    {
        $whitelistedDirectory = $this->blacklistedDirectory . '/wumpe';
        $whitelistedFile = $whitelistedDirectory . '/binNochDa';
        $mockedFileList = new GitChangeSet(
            [
                $this->blacklistedDirectory . '/sowas',
                $whitelistedFile,
                'wahwah',
                'bla',
            ],
            'asdaqwe212123'
        );
        $expectedResult = ['wahwah', 'bla', $whitelistedFile];
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn([$this->blacklistedDirectory]);
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('findTokenDirectories')
            ->once()->with($whitelistToken)->andReturn([$whitelistedDirectory]);

        $this->subject->filter($mockedFileList, '', $blacklistToken, $whitelistToken);

        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithoutFilter()
    {
        $mockedFileList = new GitChangeSet(
            [$this->blacklistedDirectory . '/sowas', 'wahwah', 'bla'],
            'asdaqwe212123'
        );
        $expectedResult = ['wahwah', 'bla'];
        $blackListToken = 'stopMe';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blackListToken, true)->andReturn([$this->blacklistedDirectory]);

        $this->subject->filter($mockedFileList, '', $blackListToken);
        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithFilter()
    {
        $mockedFilter = 'wahwah';
        $mockedFileList = new GitChangeSet([$this->blacklistedDirectory . '/mussWeg', $mockedFilter, 'bla'], 'asdaqwe212123');
        $expectedResult = [$mockedFilter];

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with('', true)->andReturn([$this->blacklistedDirectory]);

        $this->subject->filter($mockedFileList, $mockedFilter);
        MatcherAssert::assertThat(
            $mockedFileList->getFiles(),
            Matchers::arrayContainingInAnyOrder(...$expectedResult)
        );
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Console\Exception\LogicException
     *
     * @expectedExceptionCode 1553780055
     */
    public function filterThrowsExceptionIfBlackAndWhitelisted()
    {
        $mockedFileList = Mockery::mock(GitChangeSet::class);
        $blacklistToken = 'stopMe';
        $whitelistToken = 'neverMind';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($blacklistToken, false)->andReturn(['hallo']);
        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('findTokenDirectories')
            ->once()->with($whitelistToken)->andReturn(['hallo']);

        $this->subject->filter($mockedFileList, '', $blacklistToken, $whitelistToken);
    }
}
