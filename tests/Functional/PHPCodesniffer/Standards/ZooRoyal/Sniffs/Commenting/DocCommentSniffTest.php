<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\Commenting;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Process;

class DocCommentSniffTest extends TestCase
{
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
        $this->commandPrefix = explode(' ', 'php ' . self::$vendorDir . '/bin/phpcs '
            . '--sniffs=ZooRoyal.Commenting.DocComment --standard=ZooRoyal -s ');
    }

    /**
     * Hallos
     *
     * @test
     * @medium
     */
    public function processApprovesCorrectCount(): void
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
     * @medium
     */
    public function processRejectsIncorrectCount(): void
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureIncorrectComments.php';
        $this->commandPrefix[] = $fileToTest;
        $subject = new Process($this->commandPrefix, self::$vendorDir . '/../');
        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.Empty/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.ContentAfterOpen/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeShort/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.ContentBeforeClose/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.SpacingAfter/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.MissingShort/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.ShortNotCapital/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.SpacingBeforeTags/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.NonParamGroup/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.SpacingAfterTagGroup/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.TagValueIndent/', $output);
        self::assertMatchesRegularExpression('/ZooRoyal\.Commenting\.DocComment\.ParamNotFirst/', $output);
    }
}
