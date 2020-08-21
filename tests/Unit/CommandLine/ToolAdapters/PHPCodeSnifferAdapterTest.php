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
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPCodeSnifferAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPCodeSnifferAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|PHPCodeSnifferAdapter */
    private $partialSubject;
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

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->partialSubject = Mockery::mock(
            PHPCodeSnifferAdapter::class . '[!init]',
            [
                $this->mockedEnvironment,
                $this->mockedOutputInterface,
                $this->mockedGenericCommandRunner,
                $this->mockedTerminalCommandFinder,
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
        $config = '/config/phpcs/ZooRoyal/ruleset.xml';
        self::assertSame('.dontSniffPHP', $this->partialSubject->getBlacklistToken());
        self::assertSame('.php', $this->partialSubject->getFilter());
        self::assertSame('', $this->partialSubject->getBlacklistPrefix());
        self::assertSame(',', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());
        self::assertTrue($this->partialSubject->isEscape());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'PHPCSWL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
                    . $this->mockedPackageDirectory . $config . ' %1$s'
                ),
                H::hasKeyValuePair(
                    'PHPCBFWL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
                    . $this->mockedPackageDirectory . $config . ' %1$s'
                ),
                H::hasKeyValuePair(
                    'PHPCSBL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
                    . $this->mockedPackageDirectory . $config . ' --ignore=%1$s ' . $this->mockedRootDirectory
                ),
                H::hasKeyValuePair(
                    'PHPCBFBL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
                    . $this->mockedPackageDirectory . $config . ' --ignore=%1$s ' . $this->mockedRootDirectory
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
                'tool' => 'PHPCS',
                'fullMessage' => 'PHPCS : Running full check',
                'diffMessage' => 'PHPCS : Running check on diff',
                'method' => 'writeViolationsToOutput',
            ],
            'fix Violations' => [
                'tool' => 'PHPCBF',
                'fullMessage' => 'PHPCBF : Fix all Files',
                'diffMessage' => 'PHPCBF : Fix Files in diff',
                'method' => 'fixViolations',
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
        self::assertInstanceOf(FixerSupportInterface::class, $this->partialSubject);
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
