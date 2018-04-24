<?php
namespace Zooroyal\CodingStandard\Tests\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\Commenting\Fixtures;

use Exception;
use RuntimeException;

class FixtureCorrectCountOfTags
{
    /**
     * @throws RuntimeException
     */
    public function bla()
    {
        throw new RuntimeException();
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function blub()
    {
        throw new Exception();
    }
}
