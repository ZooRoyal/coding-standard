<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\ToolAdapters;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPStanAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;

class PHPStanAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|PHPStanAdapter */
    private $partialSubject;
    /* *@var MockInterface|PHPStanConfigGenerator */
    private $mockedPHPStanConfigGenerator;
    /** @var string */
    private $mockedPackageDirectory;
    /** @var string */
    private $mockedRootDirectory;
    /** @var Mockery\LegacyMockInterface|MockInterface|TerminalCommandFinder */
    private $mockedTerminalCommandFinder;

    protected function setUp()
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
        $this->mockedTerminalCommandFinder = Mockery::mock(TerminalCommandFinder::class);
        $this->mockedPHPStanConfigGenerator = Mockery::mock(PHPStanConfigGenerator::class);
        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $parameters = ['parameters' => ['excludes_analyse' => [$this->mockedRootDirectory.'/vendor']]];
        $this->mockedPHPStanConfigGenerator->shouldReceive('addConfigParameters')->once()->with('.dontStanPHP', $this->mockedRootDirectory)->andReturn($parameters);
        $this->mockedPHPStanConfigGenerator->shouldReceive('generateConfig')
            ->with($parameters)
            ->once()->andReturn('neonfilestring');
        $this->mockedPHPStanConfigGenerator->shouldReceive('writeConfig')->once()
            ->withArgs(['/package/directory/config/phpstan/phpstan.neon.dist', 'neonfilestring']);

        $this->partialSubject = Mockery::mock(
            PHPStanAdapter::class . '[!init]',
            [
                $this->mockedEnvironment,
                $this->mockedOutputInterface,
                $this->mockedGenericCommandRunner,
                $this->mockedTerminalCommandFinder,
                $this->mockedPHPStanConfigGenerator,
            ]
        )->shouldAllowMockingProtectedMethods()->makePartial();
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function constructSetsUpSubjectCorrectly()
    {
        $config = '/config/phpstan/phpstan.neon';
        self::assertSame('.dontStanPHP', $this->partialSubject->getBlacklistToken());
        self::assertSame('.php', $this->partialSubject->getFilter());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());
        self::assertFalse($this->partialSubject->isEscape());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'PHPStanBL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpstan analyse --no-progress '.$this->mockedRootDirectory.
                    ' -c ' . $this->mockedPackageDirectory . $config
                ),
                H::hasKeyValuePair(
                    'PHPStanWL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpstan analyse --no-progress -c '
                    . $this->mockedPackageDirectory . $config . ' %1$s'
                )
            )
        );
    }

    /**
     * Data Provider for callMethodsWithParametersCallsRunToolAndReturnsResult.
     *
     * @return mixed[]
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider()
    {
        return [
            'find Violations' => [
                'tool' => 'PHPStan',
                'fullMessage' => 'PHPStan : Running full check',
                'diffMessage' => 'PHPStan : Running check on diff',
                'method' => 'writeViolationsToOutput',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider
     *
     * @param string $tool
     * @param string $fullMessage
     * @param string $diffMessage
     * @param string $method
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResult(
        string $tool,
        string $fullMessage,
        string $diffMessage,
        string $method
    ) {
        $mockedProcessIsolation = true;
        $mockedTargetBranch = 'myTargetBranch';
        $expectedResult = 123123123;

        $this->partialSubject->shouldReceive('runTool')->once()
            ->with($mockedTargetBranch, $mockedProcessIsolation, $fullMessage, $tool, $diffMessage)
            ->andReturn($expectedResult);

        $result = $this->partialSubject->$method($mockedTargetBranch, $mockedProcessIsolation);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @test
     */
    public function phpCodeSnifferAdapterimplementsInterface()
    {
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
