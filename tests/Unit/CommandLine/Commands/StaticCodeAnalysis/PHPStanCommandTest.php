<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Commands\StaticCodeAnalysis;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\PHPStanCommand;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPStanAdapter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PHPStanCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var PHPStanCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp(): void
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(PHPStanCommand::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->mockedInputInterface = Mockery::mock(InputInterface::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure()
    {
        $localSubject = Mockery::mock(PHPStanCommand::class, $this->subjectParameters)
            ->shouldAllowMockingProtectedMethods()->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('sca:stan');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Run PHPStan on PHP files.');
        $localSubject->shouldReceive('setHelp')->once()
            ->with(
                'This tool executes PHPStan on a certain set of PHP files of this project.'
                . 'It ignores files which are in directories with a .dontStanPHP file. Subdirectories are ignored too.'
            );

        $localSubject->shouldReceive('getInputDefinition')->once()->withNoArgs()
            ->andReturn(new InputDefinition([new InputOption('eins'),new InputOption('zwei'),new InputOption('drei')]));
        $localSubject->shouldReceive('setDefinition')->once()->with(Mockery::on(
            function ($value) {
                MatcherAssert::assertThat($value, H::anInstanceOf(InputDefinition::class));
                /** @var InputDefinition $value */
                $options = $value->getOptions();
                MatcherAssert::assertThat(
                    $options,
                    H::allOf(
                        H::arrayWithSize(3),
                        H::everyItem(
                            H::anInstanceOf(InputOption::class)
                        )
                    )
                );
                return true;
            }
        ));
        /** @phpstan-ignore-next-line */
        $localSubject->configure();
    }


    /**
     * @test
     */
    public function writeViolationsToOutput()
    {
        $mockedTargetBranch = '';
        $mockedProcessIsolation = true;
        $expectedExitCode = 0;

        $this->prepareInputInterfaceMock($mockedTargetBranch, $mockedProcessIsolation);

        $this->subjectParameters[PHPStanAdapter::class]->shouldReceive('writeViolationsToOutput')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation)->andReturn($expectedExitCode);

        $result = $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
        self::assertSame($expectedExitCode, $result);
    }

    /**
     * This method prepares the InputInterface mocks.
     *
     * @param string $mockedTargetBranch
     * @param bool   $mockedProcessIsolation
     */
    private function prepareInputInterfaceMock(string $mockedTargetBranch, bool $mockedProcessIsolation)
    {
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('target')->andReturn($mockedTargetBranch);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('auto-target')->andReturn(null);
        $this->mockedInputInterface->shouldReceive('getOption')->once()
            ->with('process-isolation')->andReturn($mockedProcessIsolation);
    }
}
