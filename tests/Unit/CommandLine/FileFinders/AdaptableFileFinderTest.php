<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class AdaptableFileFinderTest extends AsyncTestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var AdaptableFileFinder */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(AdaptableFileFinder::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findFilesWithInvalidTargetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode('1553766210');
        $mockedTargetBranchInput = 'blaaaa';
        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')
            ->with($mockedTargetBranchInput)->andReturn(false);

        $this->subject->findFiles([], '', '', $mockedTargetBranchInput);
    }

    /**
     * Data Provider for findFilesCallsAllCheckableFileFinder.
     *
     * @return array
     */
    public function findFilesCallsAllCheckableFileFinderDataProvider(): array
    {
        return [
            'targetBranch' => [
                'targetBranchInput' => false,
                'isLocalBranch' => false,
                'finder' => AllCheckableFileFinder::class,
            ],
            'isLocalBranch' => [
                'targetBranchInput' => true,
                'isLocalBranch' => true,
                'finder' => AllCheckableFileFinder::class,
            ],
            'both' => [
                'targetBranchInput' => false,
                'isLocalBranch' => true,
                'finder' => AllCheckableFileFinder::class,
            ],
            'none' => [
                'targetBranchInput' => true,
                'isLocalBranch' => false,
                'finder' => DiffCheckableFileFinder::class,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider findFilesCallsAllCheckableFileFinderDataProvider
     *
     * @param bool $targetBranchInput
     * @param bool $isLocalBranch
     * @param string $finder
     */
    public function findFilesCallsAllCheckableFileFinder(
        bool $targetBranchInput,
        bool $isLocalBranch,
        string $finder
    ) {
        $mockedAllowedFileEndings = ['asdqwe'];
        $mockedBlacklistToken = 'qwegfasdfqwe';
        $mockedWhitelistToken = '12123sdfasdf123123';
        $expectedResult = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')
            ->with($targetBranchInput)->andReturn(true);

        $this->subjectParameters[Environment::class]->shouldReceive('isLocalBranchEqualTo')
            ->with($targetBranchInput)->andReturn($isLocalBranch);

        $this->subjectParameters[$finder]->shouldReceive('findFiles')
            ->with($mockedAllowedFileEndings, $mockedBlacklistToken, $mockedWhitelistToken, $targetBranchInput)
            ->andReturn($expectedResult);

        $result = $this->subject->findFiles(
            $mockedAllowedFileEndings,
            $mockedBlacklistToken,
            $mockedWhitelistToken,
            $targetBranchInput
        );

        self::assertSame($expectedResult, $result);
    }
}
