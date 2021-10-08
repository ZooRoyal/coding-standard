<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Zooroyal\CodingStandard\CommandLine\Library\GitInputValidator;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class GitInputValidatorTest extends TestCase
{
    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;
    private GitInputValidator $subject;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(GitInputValidator::class);
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
    public function isCommitishValidCallsProcess(): void
    {
        $mockedCommitish = 'asdasdasd';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'rev-parse', $mockedCommitish);

        $result = $this->subject->isCommitishValid($mockedCommitish);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isCommitishValidCallsProcessAndFails(): void
    {
        $mockedCommitish = 'asdasdasd';

        $this->subjectParameters[ProcessRunner::class]->shouldReceive('runAsProcess')
            ->with('git', 'rev-parse', $mockedCommitish)->andThrow(Mockery::mock(ProcessFailedException::class));

        $result = $this->subject->isCommitishValid($mockedCommitish);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isCommitishValidWithNullAndFails(): void
    {
        $result = $this->subject->isCommitishValid(null);

        self::assertFalse($result);
    }
}
