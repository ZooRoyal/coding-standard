<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\FinderFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class BlacklistFactoryTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var BlacklistFactory */
    private $subject;
    /** @var MockInterface|Finder */
    private $mockedBlacklistFinder;
    /** @var MockInterface|Finder */
    private $mockedGitFinder;
    /** @var MockInterface|Finder */
    private $mockedStopwordFinder;
    /** @var string */
    private $mockedRootDirectory = __DIR__;
    private SmartFileInfo $forgedRootDirectory;
    /** @var array<SmartFileInfo> */
    private array $blacklistedDirectories;
    /** @var string */
    private $stopWordDirectoryPath = 'config';

    protected function setUp(): void
    {
        $this->blacklistedDirectories = [
            new SmartFileInfo('vendor'),
            new SmartFileInfo('src'),
            new SmartFileInfo('tests'),
        ];

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(BlacklistFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->forgedRootDirectory = new SmartFileInfo($this->mockedRootDirectory);
        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
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
    public function getBlacklistWithNoStopword(): void
    {
        $this->prepareFindersForBlacklistWithoutStopword();

        $result = $this->subject->build();

        MatcherAssert::assertThat(
            $result,
            H::arrayContainingInAnyOrder(
                [
                    HasProperty::hasProperty('relativePathname', 'src'),
                    HasProperty::hasProperty('relativePathname', 'tests'),
                    HasProperty::hasProperty('relativePathname', 'vendor'),
                ]
            )
        );
    }

    private function prepareFindersForBlacklistWithoutStopword(): void
    {
        $this->prepareMockedGitFinder();
        $this->prepareBlacklistFinder();

        $this->subjectParameters[Environment::class]->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn($this->blacklistedDirectories);
        $this->subjectParameters[FinderFactory::class]->shouldReceive('build')
            ->withNoArgs()->andReturn($this->mockedGitFinder, $this->mockedBlacklistFinder);
    }

    private function prepareMockedGitFinder(): void
    {
        $this->mockedGitFinder = Mockery::mock(Finder::class);

        $this->mockedGitFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedGitFinder->shouldReceive('depth')->once()
            ->with('> 0')->andReturnSelf();
        $this->mockedGitFinder->shouldReceive('path')->once()
            ->with('/.git$/')->andReturnSelf();

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArrayOfDirectories')
            ->with($this->mockedGitFinder)->andReturn([new SmartFileInfo(__DIR__)]);
    }

    private function prepareBlacklistFinder(): void
    {
        $this->mockedBlacklistFinder = Mockery::mock(Finder::class);

        $this->mockedBlacklistFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedBlacklistFinder->shouldReceive('directories')->once()
            ->withNoArgs()->andReturnSelf();

        foreach ($this->blacklistedDirectories as $item) {
            $this->addPathToFinder($item);
        }

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArray')
            ->with($this->mockedBlacklistFinder)->andReturn(
                [
                    new SmartFileInfo('vendor'),
                    new SmartFileInfo('tests'),
                    new SmartFileInfo('src'),
                ]
            );
    }

    private function addPathToFinder($parameter): void
    {
        $this->mockedBlacklistFinder->shouldReceive('path')->once()
            ->with('/' . preg_quote($parameter, '/') . '$/')->andReturnSelf();
        $this->mockedBlacklistFinder->shouldReceive('notPath')->once()
            ->with('/' . preg_quote($parameter, '/') . './')->andReturnSelf();
    }

    /**
     * @test
     */
    public function getBlacklistWithStopword(): void
    {
        $foregedStopword = 'stopHere';

        $this->prepareFindersForBlacklistWithStopword($foregedStopword);

        $result = $this->subject->build($foregedStopword);

        MatcherAssert::assertThat(
            $result,
            H::arrayContainingInAnyOrder(
                [
                    HasProperty::hasProperty('relativePathname', 'config'),
                    HasProperty::hasProperty('relativePathname', 'src'),
                    HasProperty::hasProperty('relativePathname', 'tests'),
                    HasProperty::hasProperty('relativePathname', 'vendor'),
                ]
            )
        );
    }

    private function prepareFindersForBlacklistWithStopword($foregedStopword): void
    {
        $this->prepareStopwordFinder($foregedStopword);
        $this->prepareMockedGitFinder();
        $this->prepareBlacklistFinder();
        $this->subjectParameters[Environment::class]->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn($this->blacklistedDirectories);
        $this->subjectParameters[FinderFactory::class]->shouldReceive('build')
            ->withNoArgs()
            ->andReturn($this->mockedStopwordFinder, $this->mockedGitFinder, $this->mockedBlacklistFinder);
    }

    /**
     * Prepares stopword Finder Mock for successful test.
     *
     * @param string $foregedStopword
     *
     * @return string
     */
    private function prepareStopwordFinder($foregedStopword)
    {
        $this->mockedStopwordFinder = Mockery::mock(Finder::class);

        $this->mockedStopwordFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedStopwordFinder->shouldReceive('files')->once()
            ->withNoArgs()->andReturnSelf();
        $this->mockedStopwordFinder->shouldReceive('name')->once()
            ->with($foregedStopword)->andReturnSelf();

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArrayOfDirectories')
            ->with($this->mockedStopwordFinder)->andReturn([new SmartFileInfo($this->stopWordDirectoryPath)]);

        return $foregedStopword;
    }

    public function findStopwordDirectoriesUsesCacheOnMultipleCalls()
    {
        $forgedStopword = 'asd';
        $this->prepareStopwordFinder($forgedStopword);

        $this->subject->findTokenDirectories($forgedStopword);
        $this->subject->findTokenDirectories($forgedStopword);
    }
}
