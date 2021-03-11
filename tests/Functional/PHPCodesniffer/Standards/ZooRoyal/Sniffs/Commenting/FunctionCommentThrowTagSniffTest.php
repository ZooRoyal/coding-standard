<?php

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

class FunctionCommentThrowTagSniffTest extends TestCase
{
    /** @var array */
    private $commandPrefix;
    /** @var string */
    private static $vendorDir;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $reflection = new ReflectionClass(ClassLoader::class);
        self::$vendorDir = dirname(dirname($reflection->getFileName()));

        require_once self::$vendorDir . '/squizlabs/php_codesniffer/autoload.php';
    }

    protected function setUp(): void
    {
        $this->commandPrefix = explode(' ', 'vendor/bin/phpcs '
            . '--sniffs=ZooRoyal.Commenting.FunctionCommentThrowTag --standard=ZooRoyal -s ');
    }

    /**
     * Hallos
     *
     * @test
     */
    public function processApprovesCorrectCount(): void
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureCorrectCountOfTags.php';
        $this->commandPrefix[] = $fileToTest;
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');

        $subject->mustRun();
        $subject->wait();

        self::assertSame(0, $subject->getExitCode());
    }

    /**
     * @test
     */
    public function processRejectsIncorrectCount(): void
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureIncorrectCountOfTags.php';
        $this->commandPrefix[] = $fileToTest;
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');

        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        self::assertMatchesRegularExpression('/at least 2 @throws/', $output);
        self::assertMatchesRegularExpression(
            '/ZooRoyal\.Commenting\.FunctionCommentThrowTag\.WrongNumber/',
            $output
        );
    }
}
