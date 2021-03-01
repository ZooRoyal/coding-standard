<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\TypeHints;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

class DisallowMixedReturnTypeHintSniffTest extends TestCase
{
    private const SNIFF_NAME = 'Zooroyal.TypeHints.DisallowMixedReturnTypeHint';

    private const FIXTURE_DIRECTORY = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/Sniffs/TypeHints/Fixtures/ReturnType/';

    /** @var array<string> */
    private array $commandPrefix;
    private static string $vendorDir;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $reflection = new ReflectionClass(ClassLoader::class);
        self::$vendorDir = dirname($reflection->getFileName(), 2);

        require_once self::$vendorDir . '/squizlabs/php_codesniffer/autoload.php';
    }

    protected function setUp(): void
    {
        $this->commandPrefix = [
            'vendor/bin/phpcs',
            '--sniffs=' . self::SNIFF_NAME,
            '--standard=ZooRoyal',
            '-s',
        ];
    }

    /**
     * @test
     * @medium
     */
    public function itShouldReportNoErrors(): void
    {
        $this->commandPrefix[] = self::FIXTURE_DIRECTORY. 'FixtureNoMixedReturnTypeHints.php';
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');
        $subject->mustRun();
        $subject->wait();
        self::assertSame(0, $subject->getExitCode());
    }

    /**
     * @test
     * @medium
     */
    public function itShouldReportErrorsForExistingMixedTypes(): void
    {
        $this->commandPrefix[] = self::FIXTURE_DIRECTORY. 'FixtureWithMixedReturnTypeHints.php';
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');
        $subject->run();
        $subject->wait();
        $output = $subject->getOutput();
        self::assertMatchesRegularExpression('/FOUND 3 ERRORS AFFECTING 3 LINES/', $output);
        self::assertMatchesRegularExpression(
            '/ZooRoyal.TypeHints.DisallowMixedReturnTypeHint.MixedParameterTypeHintUsed/',
            $output
        );
        self::assertMatchesRegularExpression('/uses "mixed" return type hint which is disallowed/', $output);
        self::assertMatchesRegularExpression('/closure\(\) uses "mixed" return type hint which/', $output);
    }
}
