<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
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
    private $mockedRootDirectory = '/my/root';
    /** @var string[] */
    private $blacklistedDirectories = ['eins', 'weg', 'mag/nicht'];

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(BlacklistFactory::class);
        $this->subject           = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getBlacklistWithNoStopword()
    {
        $expctedResult = ['/gna/gnarz', '/bra/brarz', dirname(__DIR__)];

        $this->prepareFindersForBlacklistWithoutStopword();

        $result = $this->subject->build();

        self::assertSame($expctedResult, $result);
    }

    /**
     * @test
     */
    public function getBlacklistWithStopword()
    {
        $expctedResult   = ['/gna/gnarz', '/bra/brarz', dirname(__DIR__), dirname(__DIR__)];
        $foregedStopword = 'stopHere';

        $this->prepareFindersForBlacklistWithStopword($foregedStopword);

        $result = $this->subject->build($foregedStopword);

        self::assertSame($expctedResult, $result);
    }


    /**
     * Prepares Stopword Finder Mock for successfull test.
     *
     * @param string $foregedStopword
     *
     * @return string
     */
    private function prepareStopwordFinder($foregedStopword)
    {
        $this->mockedStopwordFinder       = Mockery::mock(Finder::class);
        $this->mockedStopwordFinder->name = 'StopwordFinder';

        $this->mockedStopwordFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedStopwordFinder->shouldReceive('files')->once()
            ->withNoArgs()->andReturnSelf();
        $this->mockedStopwordFinder->shouldReceive('name')->once()
            ->with($foregedStopword)->andReturnSelf();

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArrayOfPaths')
            ->with($this->mockedStopwordFinder)->andReturn([__DIR__]);

        return $foregedStopword;
    }

    private function prepareMockedGitFinder()
    {
        $this->mockedGitFinder       = Mockery::mock(Finder::class);
        $this->mockedGitFinder->name = 'GitFinder';

        $this->mockedGitFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedGitFinder->shouldReceive('depth')->once()
            ->with('> 0')->andReturnSelf();
        $this->mockedGitFinder->shouldReceive('path')->once()
            ->with('/.*git$/')->andReturnSelf();

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArrayOfPaths')
            ->with($this->mockedGitFinder)->andReturn([__DIR__]);
    }

    private function prepareBlacklistFinder()
    {
        $this->mockedBlacklistFinder       = Mockery::mock(Finder::class);
        $this->mockedBlacklistFinder->name = 'BlacklistFinder';

        $this->mockedBlacklistFinder->shouldReceive('in')->once()
            ->with($this->mockedRootDirectory)->andReturnSelf();
        $this->mockedBlacklistFinder->shouldReceive('directories')->once()
            ->withNoArgs()->andReturnSelf();

        foreach ($this->blacklistedDirectories as $item) {
            $this->addPathToFinder($item);
        }

        $this->subjectParameters[FinderToPathsConverter::class]->shouldReceive('finderToArrayOfPaths')
            ->with($this->mockedBlacklistFinder)->andReturn(['/gna/gnarz','/gna/gnarz/gnub', '/bra/brarz']);
    }

    private function addPathToFinder($parameter)
    {
        $this->mockedBlacklistFinder->shouldReceive('path')->once()
            ->with('/' . preg_quote($parameter, '/') . '$/')->andReturnSelf();
        $this->mockedBlacklistFinder->shouldReceive('notPath')->once()
            ->with('/' . preg_quote($parameter, '/') . './')->andReturnSelf();
    }

    private function prepareFindersForBlacklistWithoutStopword()
    {
        $this->prepareMockedGitFinder();
        $this->prepareBlacklistFinder();

        $this->subjectParameters[Environment::class]->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn($this->blacklistedDirectories);
        $this->subjectParameters[FinderFactory::class]->shouldReceive('build')
            ->withNoArgs()->andReturn($this->mockedGitFinder, $this->mockedBlacklistFinder);
    }

    private function prepareFindersForBlacklistWithStopword($foregedStopword)
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
}
