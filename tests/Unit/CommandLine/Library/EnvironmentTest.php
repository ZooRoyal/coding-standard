<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;
use function Safe\realpath;

class EnvironmentTest extends TestCase
{
    private Environment $subject;
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    /** @var MockInterface|EnhancedFileInfo  */
    private $mockedEnhancedFileInfo;

    protected function setUp(): void
    {
        $this->mockedEnhancedFileInfo = Mockery::mock(EnhancedFileInfo::class);

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(Environment::class);
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
    public function getRootDirectory(): void
    {
        $expectedPath = dirname(__DIR__, 4);

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')->once()
            ->with('git', 'rev-parse', '--show-toplevel')->andReturn($expectedPath);

        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(realpath(dirname(__DIR__, 4)))->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getRootDirectory();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  false
     */
    public function getVendorPath(): void
    {
        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(realpath(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'vendor'))
            ->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getVendorPath();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
    }

    /**
     * @test
     */
    public function getPackageDirectory(): void
    {
        $this->subjectParameters[EnhancedFileInfoFactory::class]->shouldReceive('buildFromPath')->once()
            ->with(dirname(__DIR__, 4))->andReturn($this->mockedEnhancedFileInfo);

        $result = $this->subject->getPackageDirectory();

        self::assertSame($this->mockedEnhancedFileInfo, $result);
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
            ->with('git', 'cat-file', '-t', H::either(H::containsString($mockedBranch))->andAlso(H::endsWith('^')))
            ->andReturn('commit');

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
