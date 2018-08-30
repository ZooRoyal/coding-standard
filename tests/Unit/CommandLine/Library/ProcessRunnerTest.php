<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class ProcessRunnerTest extends TestCase
{
    /** @var ProcessRunner */
    private $subject;

    protected function setUp()
    {
        $this->subject = new ProcessRunner();
    }

    /**
     * @test
     */
    public function runAsProcess()
    {
        $result = $this->subject->runAsProcess('ls');

        self::assertInternalType('string', $result);
    }

    /**
     * @test
     */
    public function runAsProcessReturningProcessObject()
    {
        $expectedResult = $this->subject->runAsProcess('ls');

        $result = $this->subject->runAsProcessReturningProcessObject('ls');

        self::assertInstanceOf(Process::class, $result);
        self::assertSame($expectedResult, trim($result->getOutput()));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function runAsProcessReturningProcessObjectWithArgumentsInjection()
    {
        $this->subject->runAsProcess('git', 'version\'; ls');
    }

    /**
     * @test
     */
    public function runProcessWithArguments()
    {
        $result = $this->subject->runAsProcess('git', 'version');

        MatcherAssert::assertThat($result, Matchers::startsWith('git version'));
        self::assertTrue(true);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function runProcessWithArgumentsInjection()
    {
        $this->subject->runAsProcess('git', 'version\'; ls');
    }
}
