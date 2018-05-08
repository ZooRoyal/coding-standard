<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Zooroyal\CodingStandard\CommandLine\Factories\FinderFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\ParentByFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;

class ParentByFileFinderTest extends TestCase
{
    /** @var MockInterface|\Zooroyal\CodingStandard\CommandLine\Library\Environment */
    private $mockedEnvironment;
    /** @var MockInterface|FinderFactory */
    private $mockedFinderFactory;
    /** @var ParentByFileFinder */
    private $subject;
    /** @var string */
    private $mockedRootDirectory = '/my/root/directory';
    /** @var MockInterface|Finder */
    private $mockedFinder;

    protected function setUp()
    {
        $this->mockedFinder = Mockery::mock(Finder::class);

        $this->mockedFinderFactory = Mockery::mock(FinderFactory::class);
        $this->mockedFinderFactory->shouldReceive('build')->withNoArgs()
            ->andReturn($this->mockedFinder);

        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')->withNoArgs()
            ->andReturn($this->mockedRootDirectory);

        $this->subject = new ParentByFileFinder($this->mockedEnvironment, $this->mockedFinderFactory);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findParentByFile()
    {
        $mockedFileName  = 'myFileName';
        $mockedDirectory = '/my/directory';
        $expectedResult  = '/my';

        $this->mockedFinder->shouldReceive('in')->once()
            ->with('/my/directory')->andReturn($this->mockedFinder);
        $this->mockedFinder->shouldReceive('in')->once()
            ->with('/my')->andReturn($this->mockedFinder);
        $this->mockedFinder->shouldReceive('files')->twice()
            ->withNoArgs()->andReturn($this->mockedFinder);
        $this->mockedFinder->shouldReceive('depth')->twice()
            ->with('== 1')->andReturn($this->mockedFinder);
        $this->mockedFinder->shouldReceive('name')->twice()
            ->with('*' . $mockedFileName . '*')->andReturn($this->mockedFinder);
        $this->mockedFinder->shouldReceive('count')->twice()
            ->withNoArgs()->andReturn(0, 1);

        $result = $this->subject->findParentByFile($mockedFileName, $mockedDirectory);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     * @expectedException     \InvalidArgumentException
     * @expectedExceptionCode 1525785151
     */
    public function findParentByFileWithNoFileNameThrowsException()
    {
        $this->subject->findParentByFile('');
    }
}
