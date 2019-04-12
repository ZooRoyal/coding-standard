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
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\JSStyleLintAdapter;
use Zooroyal\CodingStandard\CommandLine\ToolAdapters\ToolAdapterInterface;

class JSStyleLintAdapterTest extends TestCase
{
    /** @var MockInterface|Environment */
    private $mockedEnvironment;
    /** @var MockInterface|GenericCommandRunner */
    private $mockedGenericCommandRunner;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var MockInterface|JSStyleLintAdapter */
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
            JSStyleLintAdapter::class,
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
        $expectedFilter = '.less';

        self::assertSame('.dontSniffLESS', $this->partialSubject->getBlacklistToken());
        self::assertSame($expectedFilter, $this->partialSubject->getFilter());
        self::assertSame('--ignore-pattern=', $this->partialSubject->getBlacklistPrefix());
        self::assertSame(' ', $this->partialSubject->getBlacklistGlue());
        self::assertSame(' ', $this->partialSubject->getWhitelistGlue());

        MatcherAssert::assertThat(
            $this->partialSubject->getCommands(),
            H::allOf(
                H::hasKeyValuePair(
                    'STYLELINTWL',
                    $this->mockedPackageDirectory . '/node_modules/stylelint/bin/stylelint.js --config='
                    . $this->mockedPackageDirectory . '/src/config/stylelint/.stylelintrc %1$s'
                ),
                H::hasKeyValuePair(
                    'STYLELINTFIXWL',
                    $this->mockedPackageDirectory . '/node_modules/stylelint/bin/stylelint.js --config='
                    . $this->mockedPackageDirectory . '/src/config/stylelint/.stylelintrc --fix %1$s'
                ),
                H::hasKeyValuePair(
                    'STYLELINTBL',
                    $this->mockedPackageDirectory . '/node_modules/stylelint/bin/stylelint.js --config='
                    . $this->mockedPackageDirectory . '/src/config/stylelint/.stylelintrc %1$s '
                    . $this->mockedRootDirectory . '/**' . $expectedFilter
                ),
                H::hasKeyValuePair(
                    'STYLELINTFIXBL',
                    $this->mockedPackageDirectory . '/node_modules/stylelint/bin/stylelint.js --config='
                    . $this->mockedPackageDirectory . '/src/config/stylelint/.stylelintrc --fix %1$s '
                    . $this->mockedRootDirectory . '/**' . $expectedFilter
                )
            )
        );
    }

    /**
     * Data Provider for callMethodsWithParametersCallsRunToolAndReturnsResult.
     *
     * @return array
     */
    public function callMethodsWithParametersCallsRunToolAndReturnsResultDataProvider(): array
    {
        return [
            'find Violations' => [
                'tool' => 'STYLELINT',
                'fullMessage' => 'STYLELINT : Running full check',
                'diffMessage' => 'STYLELINT : Running check on diff',
                'method' => 'writeViolationsToOutput',
            ],
            'fix Violations' => [
                'tool' => 'STYLELINTFIX',
                'fullMessage' => 'STYLELINTFIX : Fix all Files',
                'diffMessage' => 'STYLELINTFIX : Fix Files in diff',
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
