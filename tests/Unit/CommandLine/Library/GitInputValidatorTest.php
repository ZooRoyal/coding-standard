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
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var GitInputValidator */
    private $subject;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory(GitInputValidator::class);
        $this->subjectParameters = $subjectFactory->buildParameters();
        $this->subject = $subjectFactory->buildSubjectInstance($this->subjectParameters);
    }

    /**
     * @test
     */
    public function isCommitishValidCallsProcess()
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
    public function isCommitishValidCallsProcessAndFails()
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
    public function isCommitishValidWithNullAndFails()
    {
        $result = $this->subject->isCommitishValid(null);

        self::assertFalse($result);
    }
}
