<?php
/**
 * Unit test class for the FunctionCommentThrowTag sniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class FunctionCommentThrowTagSniffTest extends TestCase
{
    /** @var */
    private $commandPrefix;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once '../../../../../../../vendor/squizlabs/php_codesniffer/autoload.php';
    }

    protected function setUp()
    {
        $this->commandPrefix = 'vendor/bin/phpcs '
            . '--Sniffs=ZooRoyal.Commenting.FunctionCommentThrowTag '
            . '--standard=src/config/phpcs/ZooroyalDefault/ruleset.xml '
            . '-s ';
    }

    /**
     * @test
     */
    public function processApprovesCorrectCount()
    {
        $fileToTest = 'tests/Functional/PHPCodesniffer/Standards/ZooRoyal/'
            . 'Sniffs/Commenting/Fixtures/FixtureCorrectCountOfTags.php';
        $subject    = new Process($this->commandPrefix . $fileToTest, '../../../../../../../');

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
        $subject    = new Process($this->commandPrefix . $fileToTest, '../../../../../../../');

        $subject->run();
        $subject->wait();

        $output = $subject->getOutput();
        self::assertRegExp('/at least 2 @throws/', $output);
        self::assertRegExp('/ZooRoyal\.Commenting\.FunctionCommentThrowTag\.WrongNumber/', $output);
    }
}
