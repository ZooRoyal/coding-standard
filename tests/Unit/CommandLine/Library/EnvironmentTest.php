<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class EnvironmentTest extends TestCase
{
    /** @var Environment */
    private $subject;
    /** @var string */
    private $rootDirectory;
    /** @var string[] */
    private $blacklistedDirectories = [
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
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;

    protected function setUp()
    {
        $this->rootDirectory = __DIR__;

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(Environment::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function getRootDirectory()
    {
        $mockedComposerLocator = Mockery::mock('alias:ComposerLocator');
        $mockedComposerLocator->shouldReceive('getRootPath')->once()->andReturn($this->rootDirectory);

        $result = $this->subject->getRootDirectory();

        self::assertSame($this->rootDirectory, $result);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function getPackageDirectory()
    {
        $mockedComposerLocator = Mockery::mock('alias:ComposerLocator');
        $mockedComposerLocator->shouldReceive('getPath')->once()
            ->with('zooroyal/coding-standard')->andReturn($this->rootDirectory);

        $result = $this->subject->getPackageDirectory();

        self::assertSame($this->rootDirectory, $result);
    }

    /**
     * @test
     */
    public function getNodeModulesDirectory()
    {
        $localSubject = Mockery::mock(Environment::class)->makePartial();
        $localSubject->shouldReceive('getRootDirectory')->once()
            ->andReturn($this->rootDirectory);

        $result = $localSubject->getNodeModulesDirectory();

        self::assertSame($this->rootDirectory . '/node_modules', $result);
    }

    /**
     * @test
     */
    public function getBlacklistedDirectories()
    {
        $result = $this->subject->getBlacklistedDirectories();

        self::assertSame($this->blacklistedDirectories, $result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsTrueIfCommitHashesAreEqual()
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
    public function isLocalBranchEqualToReturnsFalseIfCommitHashesAreUnequal()
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
    public function isLocalBranchEqualToCachesLocalHeadHash()
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
    public function isLocalBranchEqualToReturnsFalseIfParameterNoBranch()
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
    public function isLocalBranchEqualToWithNull()
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
    public function guessParentBranchAsCommitHashFindParent()
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
    public function guessParentBranchAsCommitHashFindNoParent()
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
