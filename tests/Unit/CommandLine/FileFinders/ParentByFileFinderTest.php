<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\CommandLine\FileFinders\ParentByFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class ParentByFileFinderTest extends AsyncTestCase
{
    /** @var ParentByFileFinder */
    private $subject;
    /** @var MockInterface[] */
    private $subjectParameters;
    /** @var string */
    private $mockedRootDirectory = '/my/root/directory';

    protected function setUp(): void
    {
        parent::setUp();
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(ParentByFileFinder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->subjectParameters[Environment::class]->shouldReceive('getRootDirectory')->withNoArgs()
            ->andReturn($this->mockedRootDirectory);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findParentByFileFindsFile()
    {
        $mockedFileName = 'myFileName';
        $mockedDirectory = '/my/directory';
        $expectedResult = '/my';

        $this->subjectParameters[Filesystem::class]->shouldReceive('exists')
            ->with($mockedDirectory . '/' . $mockedFileName)->andReturn(false);
        $this->subjectParameters[Filesystem::class]->shouldReceive('exists')
            ->with($expectedResult . '/' . $mockedFileName)->andReturn(true);

        $result = $this->subject->findParentByFile($mockedFileName, $mockedDirectory);

        self::assertSame($expectedResult, $result);
    }

    /**
     * DataProvider for findParentByFileDoesNotFindFile.
     *
     * @return mixed[]
     */
    public function findParentByFileDoesNotFindFileDataProvider() : array
    {
        return [
            'Leading /' => ['/my/directory'],
            'Leading .' => ['./my/directory'],
            'Leading RootDirectory' => ['./my/directory'],
            'No Leading string' => [$this->mockedRootDirectory . '/' . 'my/directory'],
        ];
    }

    /**
     * @test
     * @dataProvider findParentByFileDoesNotFindFileDataProvider
     *
     * @param string $mockedDirectory
     */
    public function findParentByFileDoesNotFindFile(string $mockedDirectory)
    {
        $mockedFileName = 'myFileName';

        $this->subjectParameters[Filesystem::class]->shouldReceive('exists')
            ->withAnyArgs()->andReturn(false);

        $result = $this->subject->findParentByFile($mockedFileName, $mockedDirectory);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function findParentByFileWithNoFileNameThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode('1525785151');
        $this->subject->findParentByFile('');
    }
}
