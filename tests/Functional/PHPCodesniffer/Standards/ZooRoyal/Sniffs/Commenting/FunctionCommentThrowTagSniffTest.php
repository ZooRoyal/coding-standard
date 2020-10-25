<?php

namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

class FunctionCommentThrowTagSniffTest extends TestCase
{
    /** @var string */
    private static $vendorDir;

    /** @var string */
    private $commandPrefix;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $reflection = new ReflectionClass(ClassLoader::class);
        self::$vendorDir = dirname(dirname($reflection->getFileName()));

        require_once self::$vendorDir . '/squizlabs/php_codesniffer/autoload.php';
    }

    protected function setUp()
    {
        $this->commandPrefix = 'vendor/bin/phpcs '
            . '--sniffs=ZooRoyal.Commenting.FunctionCommentThrowTag --standard=ZooRoyal -s ';
    }

    /**
     * Hallos
     *
     * @test
     */
    public function processApprovesCorrectCount()
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureCorrectCountOfTags.php';
        $commandArray = explode(' ', $this->commandPrefix . $fileToTest);
        $subject = new Process($commandArray, self::$vendorDir . '/../');

        $subject->mustRun();
        $subject->wait();

        self::assertSame(0, $subject->getExitCode());
    }

    /**
     * @test
     */
    public function processRejectsIncorrectCount()
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureIncorrectCountOfTags.php';
        $commandArray = explode(' ', $this->commandPrefix . $fileToTest);
        $subject = new Process($commandArray, self::$vendorDir . '/../');

        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        self::assertRegExp('/at least 2 @throws/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.FunctionCommentThrowTag\.WrongNumber/', $output);
    }
}
