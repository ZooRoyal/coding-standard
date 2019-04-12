<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPCopyPasteDetectorCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCopyPasteDetectorAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PHPCopyPasteDetectorCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var FindFilesToCheckCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $subjectFactory          = new SubjectFactory();
        $buildFragments          = $subjectFactory->buildSubject(PHPCopyPasteDetectorCommand::class);
        $this->subject           = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->mockedInputInterface  = Mockery::mock(InputInterface::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure()
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(PHPCopyPasteDetectorCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sca:copy-paste-detect');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run PHP-CPD on PHP files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with('This tool executes PHP-CPD on a certain set of PHP files of this project. It ignores '
                . 'files which are in directories with a .dontCopyPasteDetectPHP file. Subdirectories are ignored too.');

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function writeViolationsToOutput()
    {
        $expectedExitCode = 0;

        $this->subjectParameters[PHPCopyPasteDetectorAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->withNoArgs()->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);

        self::assertSame($expectedExitCode, $result);
    }
}
