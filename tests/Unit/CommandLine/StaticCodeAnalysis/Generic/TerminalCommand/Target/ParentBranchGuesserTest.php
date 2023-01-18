<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Process\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\ParentBranchGuesser;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class ParentBranchGuesserTest extends TestCase
{
    private ParentBranchGuesser $subject;
    /** @var array<MockInterface>|null */
    private ?array $subjectParameters = null;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(
            ParentBranchGuesser::class,
        );
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
}
