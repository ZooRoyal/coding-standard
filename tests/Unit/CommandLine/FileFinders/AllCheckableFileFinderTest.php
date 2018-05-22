<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\FileFilter;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class AllCheckableFileFinderTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var AllCheckableFileFinder */
    private $subject;

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(AllCheckableFileFinder::class);
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
    public function findAll()
    {
        $expectedFilter   = 'asd';
        $expectedStopword = 'StopMeNow';
        $expectedResult   = ['qwe'];

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with(['asd', 'qwe'], $expectedFilter, $expectedStopword)->andReturn(['qwe']);

        $result = $this->subject->findFiles($expectedFilter, $expectedStopword);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function findAllWithNoParameter()
    {
        $expectedResult   = ['qwe'];

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git ls-files')->andReturn('asd' . "\n" . 'qwe' . "\n");

        $this->subjectParameters[FileFilter::class]->shouldReceive('filterByBlacklistFilterStringAndStopword')
            ->with(['asd', 'qwe'], '', '')->andReturn(['qwe']);

        $result = $this->subject->findFiles();

        self::assertSame($expectedResult, $result);
    }
}
