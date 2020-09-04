<?php

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use ReflectionClass;
use Symfony\Component\Process\Process;

class DocCommentSniffTest extends TestCase
{
    /** @var string */
    private static $vendorDir;

    /** @var array */
    private $commandPrefix;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $reflection = new ReflectionClass(ClassLoader::class);
        self::$vendorDir = dirname(dirname($reflection->getFileName()));

        require_once self::$vendorDir . '/squizlabs/php_codesniffer/autoload.php';
    }

    protected function setUp(): void
    {
        $this->commandPrefix = explode(' ', 'php ' . self::$vendorDir . '/bin/phpcs '
            . '--sniffs=ZooRoyal.Commenting.DocComment --standard=ZooRoyal -s ');
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

        $this->commandPrefix[] = $fileToTest;
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');
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
        $this->commandPrefix[] = $fileToTest;
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');
        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        $assertRegExpMethodName = version_compare(Version::id(), '9.1', '<') ?
            'assertRegExp' : 'assertMatchesRegularExpression';

        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.Empty/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.ContentAfterOpen/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeShort/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.ContentBeforeClose/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.SpacingAfter/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.MissingShort/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.ShortNotCapital/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeTags/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.NonParamGroup/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.SpacingAfterTagGroup/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.TagValueIndent/', $output);
        self::$assertRegExpMethodName('/ZooRoyal\.Commenting\.DocComment\.ParamNotFirst/', $output);
    }
}
