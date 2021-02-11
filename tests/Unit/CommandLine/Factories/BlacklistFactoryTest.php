<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class BlacklistFactoryTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var BlacklistFactory */
    private $subject;
    /** @var string */
    private string $mockedRootDirectory;
    /** @var string[] */
    private array $blacklistedDirectories;

    protected function setUp(): void
    {
        $this->mockedRootDirectory = dirname(__DIR__, 4);
        $this->blacklistedDirectories = ['eins', 'weg', 'mag/nicht', 'tests/System'];

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(BlacklistFactory::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);
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
        $expctedResult = [$this->blacklistedDirectories[3], 'tests/Functional'];

        $this->prepareFindersForBlacklistWithoutStopword();

        $result = $this->subject->build();

        self::assertSame($expctedResult, $result);
    }

    /**
     * @test
     */
    public function getBlacklistWithStopword(): void
    {
        $expctedResult = [$this->blacklistedDirectories[3], 'tests/Unit/CommandLine/Factories', 'tests/Functional'];
        $foregedStopword = 'stopHere';

        $this->prepareFindersForBlacklistWithStopword($foregedStopword);

         $result = $this->subject->build($foregedStopword);

        self::assertSame($expctedResult, $result);
    }

    public function findStopwordDirectoriesUsesCacheOnMultipleCalls(): void
    {
        $forgedStopword = 'asd';
        $this->prepareStopwordFinder($forgedStopword);

        $this->subject->findTokenDirectories($forgedStopword);
        $this->subject->findTokenDirectories($forgedStopword);
    }

    /**
     * Prepares Stopword Finder Mock for successfull test.
     *
     * @param string $foregedStopword
     *
     * @return string
     */
    private function prepareStopwordFinder(string $foregedStopword): string
    {
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('find ' . $this->mockedRootDirectory . ' -name ' . $foregedStopword)
            ->andReturn(__DIR__ . DIRECTORY_SEPARATOR . $foregedStopword . PHP_EOL . ' ');

        return $foregedStopword;
    }

    private function prepareMockedGitFinder(): void
    {
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('find ' . $this->mockedRootDirectory . ' -type d -mindepth 2 -name .git')
            ->andReturn(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'Functional'. DIRECTORY_SEPARATOR . '.git');
    }

    private function prepareFindersForBlacklistWithoutStopword(): void
    {
        $this->prepareMockedGitFinder();

        $this->subjectParameters[Environment::class]->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn($this->blacklistedDirectories);
    }

    private function prepareFindersForBlacklistWithStopword($foregedStopword): void
    {
        $this->prepareStopwordFinder($foregedStopword);
        $this->prepareMockedGitFinder();
        $this->subjectParameters[Environment::class]->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn($this->blacklistedDirectories);
    }
}
