<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\FileFinders;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AllCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class AdaptableFileFinderTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private array $subjectParameters;
    private AdaptableFileFinder $subject;

    protected function setUp(): void
    {
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
    public function findFilesWithInvalidTargetThrowsException(): void
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
     */
    public function findFilesCallsAllCheckableFileFinderDataProvider(): array
    {
        return [
            'targetBranch' => [
                'targetBranchInput' => 'bla',
                'isCommitishValid' => true,
                'isLocalBranch' => false,
                'finder' => DiffCheckableFileFinder::class,
            ],
            'isLocalBranch' => [
                'targetBranchInput' => 'blarg',
                'isCommitishValid' => true,
                'isLocalBranch' => true,
                'finder' => AllCheckableFileFinder::class,
            ],
            'none' => [
                'targetBranchInput' => null,
                'isCommitishValid' => false,
                'isLocalBranch' => false,
                'finder' => AllCheckableFileFinder::class,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider findFilesCallsAllCheckableFileFinderDataProvider
     */
    public function findFilesCallsAllCheckableFileFinder(
        ?string $targetBranchInput,
        bool $isCommitishValid,
        bool $isLocalBranch,
        string $finder
    ): void {
        $mockedAllowedFileEndings = ['asdqwe'];
        $mockedBlacklistToken = 'qwegfasdfqwe';
        $mockedWhitelistToken = '12123sdfasdf123123';
        $expectedResult = Mockery::mock(GitChangeSet::class);

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')
            ->with($targetBranchInput)->andReturn($isCommitishValid);

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
