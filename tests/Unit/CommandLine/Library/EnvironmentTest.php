<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class EnvironmentTest extends TestCase
{
    private Environment $subject;
    /** @var string[] */
    private array $blacklistedDirectories = [
        '.eslintrc.js',
        '.git',
        '.idea',
        '.vagrant',
        'node_modules',
        'vendor',
        'bower_components',
        '.pnpm',
        '.pnpm-store',
    ];
    /** @var array<MockInterface|ProcessRunner|GitInputValidator|SmartFileInfoFactory> */
    private array $subjectParameters;
    private SmartFileInfo $forgedSmartFileInfo;
    /** @var Mockery\LegacyMockInterface|MockInterface|SmartFileInfo */
    private $mockedSmartFileInfo;

    protected function setUp(): void
    {
        $this->subjectParameters[ProcessRunner::class] = Mockery::mock(ProcessRunner::class);
        $this->subjectParameters[GitInputValidator::class] = Mockery::mock(GitInputValidator::class);
        $this->subjectParameters[SmartFileInfoFactory::class] = Mockery::mock(SmartFileInfoFactory::class);

        $this->forgedSmartFileInfo = new SmartFileInfo(__DIR__);
        $this->mockedSmartFileInfo = Mockery::mock($this->forgedSmartFileInfo);
        $this->mockedSmartFileInfo->shouldReceive('getRelativePathname')
            ->withNoArgs()->andReturn(__DIR__);

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('buildFromArrayOfPaths')->once()
            ->with($this->blacklistedDirectories)->andReturn([$this->mockedSmartFileInfo]);

        $this->subject = new Environment(
            $this->subjectParameters[ProcessRunner::class],
            $this->subjectParameters[GitInputValidator::class],
            $this->subjectParameters[SmartFileInfoFactory::class]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRootDirectory(): void
    {
        $forgedSmartFileInfo = new SmartFileInfo(__FILE__);

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(H::stringValue())->andReturn($forgedSmartFileInfo);

        $result = $this->subject->getRootDirectory();

        self::assertSame($result->getRealPath(), __FILE__);
    }

    /**
     * @test
     */
    public function getPackageDirectory(): void
    {
        $forgedSmartFileInfo = new SmartFileInfo(__FILE__);

        $this->subjectParameters[SmartFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with((dirname(__DIR__, 4)))->andReturn($forgedSmartFileInfo);

        $result = $this->subject->getPackageDirectory();

        self::assertSame($result, $forgedSmartFileInfo);
    }

    /**
     * @test
     */
    public function getBlacklistedDirectories(): void
    {
        $result = $this->subject->getBlacklistedDirectories();

        self::assertSame([$this->mockedSmartFileInfo], $result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsTrueIfCommitHashesAreEqual(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsFalseIfCommitHashesAreUnequal(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToCachesLocalHeadHash(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->twice()
            ->with($mockedBranchName)->andReturn(true);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->twice()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName)->andReturn($mockedCommitHash);

        $this->subject->isLocalBranchEqualTo($mockedBranchName);
        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsFalseIfParameterNoBranch(): void
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedLocalCommitHash = '0ewq321';

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(false);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', 'HEAD')->andReturn($mockedLocalCommitHash);
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToWithNull(): void
    {
        $mockedBranchName = null;

        $this->subjectParameters[GitInputValidator::class]->shouldReceive('isCommitishValid')->once()
            ->with($mockedBranchName)->andReturn(false);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', 'HEAD');
        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->never()
            ->with('git', 'rev-list', '-n 1', $mockedBranchName);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function guessParentBranchAsCommitHashFindParent(): void
    {
        $mockedBranch = 'myBranch';
        $expectedHash = 'asdasqweqwe12312323234';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'branch', '-a', '--contains', $mockedBranch)->andReturn('a');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'cat-file', '-t', H::containsString($mockedBranch))->andReturn('commit');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'branch', '-a', '--contains', $mockedBranch . '^')->andReturn('a' . PHP_EOL . 'b');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'rev-parse', $mockedBranch . '^')->andReturn($expectedHash);

        $result = $this->subject->guessParentBranchAsCommitHash($mockedBranch);
        self::assertSame($expectedHash, $result);
    }

    /**
     * @test
     */
    public function guessParentBranchAsCommitHashFindNoParent(): void
    {
        $mockedBranch = 'myBranch';
        $expectedHash = 'asdasqweqwe12312323234';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'branch', '-a', '--contains', $mockedBranch)->andReturn('a');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'cat-file', '-t', H::containsString($mockedBranch))->andReturn('blarb');

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'rev-parse', $mockedBranch)->andReturn($expectedHash);

        $result = $this->subject->guessParentBranchAsCommitHash($mockedBranch);
        self::assertSame($expectedHash, $result);
    }
}
