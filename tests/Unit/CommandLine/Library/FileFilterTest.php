<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class FileFilterTest extends TestCase
{
    /** @var FileFilter */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;
    /** @var string */
    private $blacklistedEntry = 'blub';

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(FileFilter::class);
        $this->subject           = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithoutFilter()
    {
        $mockedFileList = new GitChangeSet([$this->blacklistedEntry, 'wahwah', 'bla'], 'asdaqwe212123');
        $expectedResult = [1 => 'wahwah', 2 => 'bla'];
        $stopword       = 'stopMe';

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with($stopword)->andReturn([$this->blacklistedEntry]);

        $this->subject->filterByBlacklistFilterStringAndStopword($mockedFileList, '', $stopword);
        self::assertEquals($expectedResult, $mockedFileList->getFiles());
    }

    /**
     * @test
     */
    public function filterByBlacklistAndFilterStringWithFilter()
    {
        $mockedFilter   = 'wahwah';
        $mockedFileList = new GitChangeSet([$this->blacklistedEntry, $mockedFilter, 'bla'], 'asdaqwe212123');
        $expectedResult = [1 => $mockedFilter];

        $this->subjectParameters[BlacklistFactory::class]->shouldReceive('build')
            ->once()->with('')->andReturn([$this->blacklistedEntry]);

        $this->subject->filterByBlacklistFilterStringAndStopword($mockedFileList, $mockedFilter);
        self::assertEquals($expectedResult, $mockedFileList->getFiles());
    }
}
