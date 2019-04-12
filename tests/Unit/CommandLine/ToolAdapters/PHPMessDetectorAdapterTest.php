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
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\FixerSupportInterface;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\PHPMessDetectorAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class PHPMessDetectorAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|PHPMessDetectorAdapter */
    private $partialSubject;
    /** @var string */
    private $mockedPackageDirectory;
    /** @var string */
    private $mockedRootDirectory;

    protected function setUp()
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedGenericCommandRunner = Mockery::mock(GenericCommandRunner::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);

        $this->mockedPackageDirectory = '/package/directory';
        $this->mockedRootDirectory = '/root/directory';

        $this->mockedEnvironment->shouldReceive('getPackageDirectory')
            ->withNoArgs()->andReturn('' . $this->mockedPackageDirectory);
        $this->mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($this->mockedRootDirectory);

        $this->partialSubject = Mockery::mock(
            PHPMessDetectorAdapter::class,
            [$this->mockedEnvironment, $this->mockedOutputInterface, $this->mockedGenericCommandRunner]
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
        self::assertSame('.dontMessDetectPHP', $this->partialSubject->getBlacklistToken());
        self::assertSame('.php', $this->partialSubject->getFilter());
        self::assertSame('', $this->partialSubject->getBlacklistPrefix());
        self::assertSame(',', $this->partialSubject->getBlacklistGlue());
        self::assertSame(',', $this->partialSubject->getWhitelistGlue());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'PHPMDWL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpmd %1$s text ' . $this->mockedPackageDirectory
                    . '/src/config/phpmd/ZooRoyalDefault/phpmd.xml --suffixes php'
                ),
                H::hasKeyValuePair(
                    'PHPMDBL',
                    'php ' . $this->mockedRootDirectory . '/vendor/bin/phpmd ' . $this->mockedRootDirectory
                    . ' text ' . $this->mockedPackageDirectory
                    . '/src/config/phpmd/ZooRoyalDefault/phpmd.xml --suffixes php --exclude %1$s'
                )
            )
        );
    }

    /**
     * Data Provider for callMethodsWithParametersCallsRunToolAndReturnsResult.
     *
     * @return array
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider()
    {
        return [
            'find Violations' => [
                'tool' => 'PHPMD',
                'fullMessage' => 'PHPMD : Running full check',
                'diffMessage' => 'PHPMD : Running check on diff',
                'method' => 'writeViolationsToOutput'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider
     *
     * @param $tool
     * @param $fullMessage
     * @param $diffMessage
     * @param $method
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResult($tool, $fullMessage, $diffMessage, $method)
    {
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
        self::assertNotInstanceOf(FixerSupportInterface::class, $this->partialSubject);
        self::assertInstanceOf(ToolAdapterInterface::class, $this->partialSubject);
    }
}
