<?php

namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

class DocCommentSniffTest extends TestCase
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
        $this->commandPrefix = 'php ' . self::$vendorDir . '/bin/phpcs '
            . '--sniffs=ZooRoyal.Commenting.DocComment --standard=ZooRoyal -s ';
    }

    /**
     * Hallos
     *
     * @test
     */
    public function processApprovesCorrectCount()
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureCorrectComments.php';

        $subject = new Process(array_merge(explode(' ', $this->commandPrefix), [$fileToTest]), self::$vendorDir . '/../');
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
            . 'Sniffs/Commenting/Fixtures/FixtureIncorrectComments.php';

        $subject = new Process(array_merge(explode(' ', $this->commandPrefix), [$fileToTest]), self::$vendorDir . '/../');
        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.Empty/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.ContentAfterOpen/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeShort/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.ContentBeforeClose/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.SpacingAfter/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.MissingShort/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.ShortNotCapital/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeTags/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.NonParamGroup/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.SpacingAfterTagGroup/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.TagValueIndent/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.DocComment\.ParamNotFirst/', $output);
    }
}
